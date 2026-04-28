<?php

// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Palette;
use App\Models\Warning;
use App\Models\UserNotification;
use App\Models\StatsHistory;
use App\Models\Post;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReport;
use App\Models\Appeal;
use App\Models\AppealImage;
use App\Models\Follow; // ✅ FIXED: was missing, caused 500 on /users/{user}/profile

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Register
Route::post('/register', function (Request $request) {
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'User registered successfully',
        'token'   => $token,
        'user'    => $user,
    ], 201);
});

// Login
Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    if ($user->is_banned) {
        // Auto-unban if expired
        if ($user->ban_expires_at && now()->gt($user->ban_expires_at)) {
            $user->is_banned = false;
            $user->ban_expires_at = null;
            $user->save();
        } else {
            return response()->json([
                'message'        => 'Your account has been banned.',
                'is_banned'      => true,
                'ban_duration'   => $user->ban_duration,
                'ban_expires_at' => $user->ban_expires_at,
                'ban_reason'     => $user->ban_reason,
            ], 403);
        }
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token'   => $token,
        'user'    => $user,
    ]);
});

// Palette search proxy (public)
Route::get('/palette/search', function (Request $request) {
    $q = $request->query('q', '');
    $response = \Illuminate\Support\Facades\Http::get('https://colormagic.app/api/palette/search', [
        'q' => $q,
    ]);
    return response()->json($response->json());
});

// Colormind proxy (public)
Route::post('/palette', function (Request $request) {
    $response = \Illuminate\Support\Facades\Http::post('http://colormind.io/api/', $request->all());
    return response()->json($response->json());
});

// Get posts (public feed)
Route::get('/posts', function (Request $request) {
    $category = $request->query('category', 'all');
    $sort     = $request->query('sort', 'latest');
    $search   = $request->query('search', '');
    $type     = $request->query('type', 'posts');

    if ($type === 'people') {
        $users = User::when($search, fn($q) =>
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
        )
        ->withCount('posts')
        ->latest()
        ->paginate(20);
        return response()->json($users);
    }

    $query = Post::with('user:id,name,avatar,role,bio')
        ->withCount([
            'likes as liked_by_user' => function ($q) use ($request) {
                $q->where('user_id', $request->user()?->id ?? 0);
            },
            'likes as likes_count'
        ])
        ->when($category !== 'all', fn($q) => $q->where('category', $category))
        ->when($search, fn($q) =>
            $q->where('caption', 'like', "%$search%")
              ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
        );

    if ($sort === 'popular') {
        $query->orderByDesc('likes_count');
    } else {
        $query->latest();
    }

    return $query->paginate(12);
});

// Get post comments (public)
Route::get('/posts/{post}/comments', function (\App\Models\Post $post, Request $request) {
    return Comment::where('post_id', $post->id)
        ->whereNull('parent_id')
        ->with([
            'user:id,name,avatar,role',
            'replies.user:id,name,avatar,role',
        ])
        ->withCount([
            'likes as liked_by_user' => fn($q) => $q->where('user_id', $request->user()?->id ?? 0),
            'likes as likes_count',
        ])
        ->latest()
        ->get();
});

// ✅ FIXED: moved outside auth middleware so it works as a public profile page
// (auth is optional — liked_by_user falls back to 0 for guests)
Route::get('/users/{user}/profile', function (\App\Models\User $user, Request $request) {
    $posts = Post::where('user_id', $user->id)
        ->with('user:id,name,avatar')
        ->withCount(['likes as liked_by_user' => function ($q) use ($request) {
            $q->where('user_id', $request->user()?->id ?? 0);
        }])
        ->latest()->get();

    $palettes = Palette::where('user_id', $user->id)->latest()->get();

    $followersCount = Follow::where('following_id', $user->id)->count();
    $followingCount = Follow::where('follower_id', $user->id)->count();
    $isFollowing    = $request->user()
        ? Follow::where('follower_id', $request->user()->id)->where('following_id', $user->id)->exists()
        : false;

    return response()->json([
        'user' => [
            'id'              => $user->id,
            'name'            => $user->name,
            'avatar'          => $user->avatar,
            'bio'             => $user->bio,
            'role'            => $user->role,
            'created_at'      => $user->created_at,
            'email'           => $user->email,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'is_following'    => $isFollowing,
        ],
        'posts'    => $posts,
        'palettes' => $palettes,
    ]);
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    // Get current user
    Route::get('/me', function (Request $request) {
        $user = $request->user();
        // Auto-unban if expired
        if ($user->is_banned && $user->ban_expires_at && now()->gt($user->ban_expires_at)) {
            $user->is_banned = false;
            $user->ban_expires_at = null;
            $user->save();
        }
        return response()->json($user->only([
            'id', 'name', 'email', 'role', 'avatar', 'bio',
            'is_banned', 'strikes', 'created_at', 'ban_expires_at', 'ban_reason', 'ban_duration'
        ]));
    });

    // ✅ "Clear all" MUST come before the wildcard {palette} route
    Route::delete('/palettes/all', function (Request $request) {
        $request->user()->palettes()->delete();
        return response()->json(['message' => 'All palettes cleared']);
    });

    // Get all palettes
    Route::get('/palettes', function (Request $request) {
        return $request->user()->palettes()->latest()->get();
    });

    // Save palette
    Route::post('/palettes', function (Request $request) {
        $request->validate([
            'name'   => 'required|string|max:255',
            'colors' => 'required|array',
            'source' => 'required|in:image,keyword,created',
        ]);

        $palette = $request->user()->palettes()->create([
            'name'   => $request->name,
            'colors' => $request->colors,
            'source' => $request->source,
        ]);

        return response()->json($palette, 201);
    });

    // Delete single palette
    Route::delete('/palettes/{palette}', function (Request $request, Palette $palette) {
        if ($palette->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $palette->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // Update name
    Route::put('/user/name', function (Request $request) {
        $request->validate(['name' => 'required|string|max:255']);
        $user = $request->user();
        $user->name = $request->name;
        $user->save();
        return response()->json(['message' => 'Name updated']);
    });

    // Change password
    Route::put('/user/password', function (Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6',
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Wrong password'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'Password updated']);
    });

    // Upload avatar
    Route::post('/user/avatar', function (Request $request) {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $path = $request->file('avatar')->store('avatars', 'public');
        $user = $request->user();
        $user->avatar = $path;
        $user->save();
        return response()->json(['avatar' => $path]);
    });

    // Delete account
    Route::delete('/user', function (Request $request) {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'Account deleted']);
    });

    // Update bio
    Route::put('/user/bio', function (Request $request) {
        $request->validate(['bio' => 'nullable|string|max:500']);
        $user = $request->user();
        $user->bio = $request->bio;
        $user->save();
        return response()->json(['message' => 'Bio updated']);
    });

    // Get user's own posts
    Route::get('/my-posts', function (Request $request) {
        return $request->user()->posts()->with('user:id,name,avatar')
            ->withCount(['likes as liked_by_user' => function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            }])
            ->latest()->get();
    });

    // Save / unsave post toggle
    Route::post('/posts/{post}/save', function (Request $request, Post $post) {
        $existing = \App\Models\SavedPost::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)->first();
        if ($existing) {
            $existing->delete();
            $post->decrement('saves_count');
            return response()->json(['saved' => false]);
        }
        \App\Models\SavedPost::create(['post_id' => $post->id, 'user_id' => $request->user()->id]);
        $post->increment('saves_count');
        return response()->json(['saved' => true]);
    });

    // Get saved posts
    Route::get('/saved-posts', function (Request $request) {
        $savedPostIds = \App\Models\SavedPost::where('user_id', $request->user()->id)->pluck('post_id');
        return Post::whereIn('id', $savedPostIds)
            ->with('user:id,name,avatar')
            ->withCount(['likes as liked_by_user' => function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            }])
            ->latest()->get();
    });

    // Create post
    Route::post('/posts', function (Request $request) {
        $request->validate([
            'image'     => 'nullable|image|max:5120',
            'caption'   => 'nullable|string|max:500',
            'colors'    => 'nullable|array',
            'category'  => 'nullable|string|max:100',
            'post_type' => 'nullable|in:creation,palette',
        ]);

        $postType = $request->post_type ?? 'creation';
        $category = $postType === 'palette' ? 'Palette' : ($request->category ?? 'Other');

        if ($postType === 'creation' && !$request->hasFile('image')) {
            return response()->json(['message' => 'Image is required for creation posts.'], 422);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id'   => $request->user()->id,
            'image'     => $path,
            'caption'   => $request->caption,
            'colors'    => $request->colors ?? [],
            'category'  => $category,
            'post_type' => $postType,
        ]);

        return response()->json($post->load('user:id,name,avatar'), 201);
    });

    // Edit own post
    Route::put('/posts/{post}', function (Request $request, Post $post) {
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'caption'  => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'colors'   => 'nullable|array',
        ]);
        $post->caption  = $request->caption;
        $post->category = $request->category ?? $post->category;
        $post->colors   = $request->colors ?? $post->colors;
        $post->save();
        return response()->json($post->load('user:id,name,avatar'));
    });

    // Delete own post
    Route::delete('/posts/{post}', function (Request $request, Post $post) {
        if ($post->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    });

    // Like / Unlike toggle
    // ✅ FIXED: notification was after return (unreachable) — moved before return
    Route::post('/posts/{post}/like', function (Request $request, Post $post) {
        $existing = \App\Models\PostLike::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            return response()->json(['liked' => false, 'likes_count' => $post->fresh()->likes_count]);
        }

        \App\Models\PostLike::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);
        $post->increment('likes_count');

        // ✅ Notify post owner (must be before return)
        if ($post->user_id !== $request->user()->id) {
            UserNotification::create([
                'user_id' => $post->user_id,
                'type'    => 'like',
                'title'   => '❤️ New Like',
                'message' => "{$request->user()->name} liked your post.",
                'data'    => [
                    'post_id'      => $post->id,
                    'liker_id'     => $request->user()->id,
                    'liker_name'   => $request->user()->name,
                    'liker_avatar' => $request->user()->avatar,
                    'post_image'   => $post->image,
                ],
            ]);
        }

        return response()->json(['liked' => true, 'likes_count' => $post->fresh()->likes_count]);
    });

    // Report post
    Route::post('/posts/{post}/report', function (Request $request, Post $post) {
        $request->validate([
            'topic'   => 'required|in:spam,inappropriate,harassment,copyright,other',
            'details' => 'nullable|string|max:500',
        ]);

        $existing = \App\Models\Report::where('post_id', $post->id)
            ->where('reporter_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You already reported this post.'], 422);
        }

        \App\Models\Report::create([
            'post_id'     => $post->id,
            'reporter_id' => $request->user()->id,
            'topic'       => $request->topic,
            'details'     => $request->details,
        ]);

        return response()->json(['message' => 'Report submitted.']);
    });

    // Post comment
    // ✅ FIXED: notification was in GET route — moved here to POST where it belongs
    Route::post('/posts/{post}/comments', function (Request $request, Post $post) {
        $request->validate([
            'content'   => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
        $comment = Comment::create([
            'post_id'   => $post->id,
            'user_id'   => $request->user()->id,
            'parent_id' => $request->parent_id,
            'content'   => $request->content,
        ]);

        // Notify post owner on new comment (not on replies to avoid noise)
        if (!$request->parent_id && $post->user_id !== $request->user()->id) {
            UserNotification::create([
                'user_id' => $post->user_id,
                'type'    => 'comment',
                'title'   => '💬 New Comment',
                'message' => "{$request->user()->name} commented: \"{$request->content}\"",
                'data'    => [
                    'post_id'         => $post->id,
                    'commenter_id'    => $request->user()->id,
                    'commenter_name'  => $request->user()->name,
                    'commenter_avatar'=> $request->user()->avatar,
                    'comment_preview' => substr($request->content, 0, 50),
                ],
            ]);
        }

        return $comment->load('user:id,name,avatar,role');
    });

    // Delete comment
    Route::delete('/comments/{comment}', function (Request $request, Comment $comment) {
        if ($comment->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $comment->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // Like / Unlike comment
    Route::post('/comments/{comment}/like', function (Request $request, Comment $comment) {
        $existing = CommentLike::where('comment_id', $comment->id)
            ->where('user_id', $request->user()->id)->first();
        if ($existing) {
            $existing->delete();
            $comment->decrement('likes_count');
            return response()->json(['liked' => false, 'likes_count' => $comment->fresh()->likes_count]);
        }
        CommentLike::create(['comment_id' => $comment->id, 'user_id' => $request->user()->id]);
        $comment->increment('likes_count');
        return response()->json(['liked' => true, 'likes_count' => $comment->fresh()->likes_count]);
    });

    // Report comment
    Route::post('/comments/{comment}/report', function (Request $request, Comment $comment) {
        $request->validate([
            'topic'   => 'required|in:spam,inappropriate,harassment,copyright,other',
            'details' => 'nullable|string|max:500',
        ]);
        CommentReport::create([
            'comment_id'  => $comment->id,
            'reporter_id' => $request->user()->id,
            'topic'       => $request->topic,
            'details'     => $request->details,
        ]);
        return response()->json(['message' => 'Comment reported.']);
    });

    // Get user notifications
    Route::get('/notifications', function (Request $request) {
        return UserNotification::where('user_id', $request->user()->id)
            ->latest()->get();
    });

    // Mark notification as read
    Route::patch('/notifications/{notification}/read', function (Request $request, UserNotification $notification) {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $notification->read_at = now();
        $notification->save();
        return response()->json(['message' => 'Marked as read']);
    });

    // Mark all notifications as read
    Route::patch('/notifications/read-all', function (Request $request) {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
    });

    // Delete all notifications permanently
    Route::delete('/notifications', function (Request $request) {
        UserNotification::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Notifications cleared.']);
    });

    // Submit appeal for a warning
    Route::post('/warnings/{warning}/appeal', function (Request $request, Warning $warning) {
        if ($warning->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($warning->appeal) {
            return response()->json(['message' => 'You already submitted an appeal for this warning.'], 422);
        }
        $request->validate([
            'apology_text' => 'required|string|max:1000',
            'images'       => 'nullable|array|max:5',
            'images.*'     => 'image|max:3072',
        ]);

        $appeal = Appeal::create([
            'warning_id'   => $warning->id,
            'user_id'      => $request->user()->id,
            'apology_text' => $request->apology_text,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('appeals', 'public');
                AppealImage::create(['appeal_id' => $appeal->id, 'image' => $path]);
            }
        }

        $warning->status = 'reviewed';
        $warning->save();

        return response()->json($appeal->load('images'), 201);
    });

    // Get my warnings
    Route::get('/my-warnings', function (Request $request) {
        $warnings = Warning::where('user_id', $request->user()->id)
            ->with(['post:id,image,caption,category,colors', 'appeal.images'])
            ->latest()
            ->get();

        return $warnings->map(function ($w) {
            return array_merge($w->toArray(), ['id' => $w->id]);
        });
    });

    // Get my appeals
    Route::get('/my-appeals', function (Request $request) {
        return Appeal::where('user_id', $request->user()->id)
            ->with(['warning.post', 'images', 'reviewer:id,name'])
            ->latest()
            ->get();
    });

    // Toggle follow/unfollow
    Route::post('/users/{user}/follow', function (Request $request, User $user) {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot follow yourself.'], 422);
        }

        $existing = Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'following'       => false,
                'followers_count' => Follow::where('following_id', $user->id)->count(),
            ]);
        }

        Follow::create(['follower_id' => $request->user()->id, 'following_id' => $user->id]);

        UserNotification::create([
            'user_id' => $user->id,
            'type'    => 'follow',
            'title'   => '👤 New Follower',
            'message' => "{$request->user()->name} started following you.",
            'data'    => [
                'follower_id'     => $request->user()->id,
                'follower_name'   => $request->user()->name,
                'follower_avatar' => $request->user()->avatar,
            ],
        ]);

        return response()->json([
            'following'       => true,
            'followers_count' => Follow::where('following_id', $user->id)->count(),
        ]);
    });

    // Get followers of a user
    Route::get('/users/{user}/followers', function (User $user) {
        return Follow::where('following_id', $user->id)
            ->with('follower:id,name,avatar,role,bio')
            ->latest()->get()
            ->pluck('follower');
    });

    // Get users that a user follows
    Route::get('/users/{user}/following', function (User $user) {
        return Follow::where('follower_id', $user->id)
            ->with('following:id,name,avatar,role,bio')
            ->latest()->get()
            ->pluck('following');
    });

    // Check if current user follows a specific user
    Route::get('/users/{user}/is-following', function (Request $request, User $user) {
        $isFollowing = Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $user->id)->exists();
        return response()->json(['following' => $isFollowing]);
    });

});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

    // Dashboard stats
    Route::get('/stats', function () {
        return response()->json([
            'total_users'          => User::count(),
            'total_palettes'       => Palette::count(),
            'total_posts'          => Post::count(),
            'by_source'            => [
                'image'   => Palette::where('source', 'image')->count(),
                'keyword' => Palette::where('source', 'keyword')->count(),
                'created' => Palette::where('source', 'created')->count(),
            ],
            'new_users_this_week'  => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->subMonth())->count(),
        ]);
    });

    // Debug route
    Route::get('/debug-me', function (Request $request) {
        return response()->json([
            'user'    => $request->user(),
            'role'    => $request->user()?->role,
            'isAdmin' => $request->user()?->isAdmin(),
        ]);
    });

    // Get all users
    Route::get('/users', function (Request $request) {
        $search = $request->query('search', '');
        return User::when($search, fn($q) =>
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
        )
        ->withCount('palettes')
        ->latest()
        ->get();
    });

    // Get single user
    Route::get('/users/{user}', function (User $user) {
        return $user->loadCount('palettes')->load('palettes');
    });

    // Ban / Unban user
    Route::patch('/users/{user}/ban', function (Request $request, User $user) {
        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Cannot ban a super admin.'], 403);
        }

        if ($user->is_banned) {
            $user->is_banned      = false;
            $user->ban_expires_at = null;
            $user->ban_reason     = null;
            $user->ban_duration   = null;
            $user->save();
            return response()->json(['message' => 'User unbanned.', 'is_banned' => false]);
        }

        $request->validate([
            'duration'     => 'required|in:1d,3d,1w,1m,3m,1y,permanent',
            'admin_reason' => 'nullable|string|max:500',
        ]);

        $durationMap = [
            '1d'        => ['label' => '1 day',     'days' => 1],
            '3d'        => ['label' => '3 days',     'days' => 3],
            '1w'        => ['label' => '1 week',     'days' => 7],
            '1m'        => ['label' => '1 month',    'days' => 30],
            '3m'        => ['label' => '3 months',   'days' => 90],
            '1y'        => ['label' => '1 year',     'days' => 365],
            'permanent' => ['label' => 'permanent',  'days' => null],
        ];

        $dur       = $durationMap[$request->duration];
        $expiresAt = $dur['days'] ? now()->addDays($dur['days']) : null;

        $user->is_banned      = true;
        $user->ban_expires_at = $expiresAt;
        $user->ban_reason     = $request->admin_reason;
        $user->ban_duration   = $dur['label'];
        $user->save();

        UserNotification::create([
            'user_id' => $user->id,
            'type'    => 'warning',
            'title'   => '🚫 Account Banned',
            'message' => "Your account has been banned for {$dur['label']}." . ($request->admin_reason ? " Reason: {$request->admin_reason}" : ''),
            'data'    => [
                'ban_duration'   => $dur['label'],
                'ban_expires_at' => $expiresAt,
                'admin_reason'   => $request->admin_reason,
            ],
        ]);

        return response()->json([
            'message'        => "User banned for {$dur['label']}.",
            'is_banned'      => true,
            'ban_expires_at' => $expiresAt,
        ]);
    });

    // Direct ban from report
    Route::post('/users/{user}/direct-ban', function (Request $request, User $user) {
        $request->validate([
            'report_category' => 'required|in:spam,inappropriate,harassment,copyright,other',
            'duration'        => 'required|in:1d,3d,1w,1m,3m,1y,permanent',
            'admin_reason'    => 'nullable|string|max:500',
        ]);

        $durationMap = [
            '1d'        => ['label' => '1 day',    'days' => 1],
            '3d'        => ['label' => '3 days',   'days' => 3],
            '1w'        => ['label' => '1 week',   'days' => 7],
            '1m'        => ['label' => '1 month',  'days' => 30],
            '3m'        => ['label' => '3 months', 'days' => 90],
            '1y'        => ['label' => '1 year',   'days' => 365],
            'permanent' => ['label' => 'permanent','days' => null],
        ];

        $autoCaptions = [
            'spam'          => 'Account banned for spam violations.',
            'inappropriate' => 'Account banned for inappropriate content.',
            'harassment'    => 'Account banned for harassment.',
            'copyright'     => 'Account banned for copyright violations.',
            'other'         => 'Account banned for community guideline violations.',
        ];

        $dur       = $durationMap[$request->duration];
        $expiresAt = $dur['days'] ? now()->addDays($dur['days']) : null;

        $user->is_banned      = true;
        $user->ban_expires_at = $expiresAt;
        $user->ban_reason     = $request->admin_reason ?: $autoCaptions[$request->report_category];
        $user->ban_duration   = $dur['label'];
        $user->save();

        UserNotification::create([
            'user_id' => $user->id,
            'type'    => 'warning',
            'title'   => '🚫 Account Banned',
            'message' => $autoCaptions[$request->report_category] . " Duration: {$dur['label']}." . ($request->admin_reason ? " Admin note: {$request->admin_reason}" : ''),
            'data'    => [
                'ban_duration'    => $dur['label'],
                'ban_expires_at'  => $expiresAt,
                'report_category' => $request->report_category,
            ],
        ]);

        return response()->json(['message' => "User banned for {$dur['label']}."]);
    });

    // Delete user
    Route::delete('/users/{user}', function (Request $request, User $user) {
        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Cannot delete a super admin.'], 403);
        }
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    });

    // Get all palettes
    Route::get('/palettes', function (Request $request) {
        $search = $request->query('search', '');
        $query  = Palette::with('user:id,name,email')->latest();
        if ($request->query('source') && $request->query('source') !== 'all') {
            $query->where('source', $request->query('source'));
        }
        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%$search%"));
        }
        return $query->paginate(20);
    });

    // Delete any palette
    Route::delete('/palettes/{palette}', function (Palette $palette) {
        $palette->delete();
        return response()->json(['message' => 'Palette deleted.']);
    });

    // Get all reports
    Route::get('/reports', function (Request $request) {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $sort   = $request->query('sort', 'newest');

        $query = \App\Models\Report::with([
            'post:id,image,caption,category,colors,user_id,likes_count,saves_count',
            'post.user:id,name,avatar',
            'reporter:id,name,avatar',
        ])
        ->when($status !== 'all', fn($q) => $q->where('status', $status))
        ->when($search, fn($q) =>
            $q->whereHas('reporter', fn($u) => $u->where('name', 'like', "%$search%"))
            ->orWhereHas('post', fn($p) => $p->where('caption', 'like', "%$search%"))
        );

        match ($sort) {
            'oldest'     => $query->oldest(),
            'this_week'  => $query->where('created_at', '>=', now()->startOfWeek())->latest(),
            'last_week'  => $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->latest(),
            'last_month' => $query->where('created_at', '>=', now()->subMonth())->latest(),
            'last_year'  => $query->where('created_at', '>=', now()->subYear())->latest(),
            default      => $query->latest(),
        };

        return $query->paginate(20);
    });

    // Update report status
    Route::patch('/reports/{report}/status', function (Request $request, \App\Models\Report $report) {
        $request->validate(['status' => 'required|in:pending,reviewed,dismissed']);
        $report->status = $request->status;
        $report->save();
        return response()->json(['message' => 'Report updated.']);
    });

    // Admin delete any post
    Route::delete('/posts/{post}', function (Post $post) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
        $post->delete();
        return response()->json(['message' => 'Post deleted by admin.']);
    });

    // Get comment reports
    Route::get('/comment-reports', function (Request $request) {
        $search = $request->query('search', '');
        return CommentReport::with([
            'comment:id,post_id,user_id,content',
            'comment.user:id,name,avatar',
            'comment.post:id,image,caption,category,colors,user_id',
            'comment.post.user:id,name,avatar',
            'reporter:id,name,avatar',
        ])
        ->when($search, fn($q) =>
            $q->whereHas('reporter', fn($u) => $u->where('name', 'like', "%$search%"))
            ->orWhereHas('comment', fn($c) => $c->where('content', 'like', "%$search%"))
        )
        ->latest()
        ->paginate(20);
    });

    // Send warning to user
    Route::post('/warnings', function (Request $request) {
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'post_id'         => 'nullable|exists:posts,id',
            'report_category' => 'required|in:spam,inappropriate,harassment,copyright,other',
            'admin_text'      => 'nullable|string|max:500',
            'expires_days'    => 'required|in:1,3,5',
        ]);

        $autoCaptions = [
            'spam'          => 'Your post has been flagged for spam. Repeated violations will result in a ban.',
            'inappropriate' => 'Your post contains inappropriate content that violates our community guidelines.',
            'harassment'    => 'Your post has been flagged for harassment. This behavior is not tolerated.',
            'copyright'     => 'Your post may contain copyrighted content without proper attribution.',
            'other'         => 'Your post has been flagged for violating our community guidelines.',
        ];

        $expiresAt = now()->addDays($request->expires_days);

        $warning = Warning::create([
            'user_id'         => $request->user_id,
            'admin_id'        => $request->user()->id,
            'post_id'         => $request->post_id,
            'report_category' => $request->report_category,
            'auto_caption'    => $autoCaptions[$request->report_category],
            'admin_text'      => $request->admin_text,
            'expires_days'    => $request->expires_days,
            'expires_at'      => $expiresAt,
        ]);

        UserNotification::create([
            'user_id' => $request->user_id,
            'type'    => 'warning',
            'title'   => '⚠️ Warning from Admin',
            'message' => $autoCaptions[$request->report_category],
            'data'    => [
                'warning_id'      => $warning->id,
                'report_category' => $request->report_category,
                'auto_caption'    => $autoCaptions[$request->report_category],
                'admin_text'      => $request->admin_text,
                'expires_at'      => $expiresAt,
                'expires_days'    => $request->expires_days,
                'post_id'         => $request->post_id,
            ],
        ]);

        return response()->json(['message' => 'Warning sent.', 'warning' => $warning]);
    });

    // Daily stats
    Route::get('/stats/daily', function () {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date'     => $date->format('M d'),
                'users'    => User::whereDate('created_at', $date)->count(),
                'posts'    => Post::whereDate('created_at', $date)->count(),
                'palettes' => Palette::whereDate('created_at', $date)->count(),
            ];
        });
        return response()->json($days);
    });

    // Monthly stats
    Route::get('/stats/monthly', function () {
        $months = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month'    => $date->format('M Y'),
                'users'    => User::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'posts'    => Post::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'palettes' => Palette::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        });
        return response()->json($months);
    });

    // Archive yearly stats
    Route::post('/stats/archive', function () {
        $year = now()->subYear()->year;
        $data = [
            'total_users'    => User::whereYear('created_at', $year)->count(),
            'total_posts'    => Post::whereYear('created_at', $year)->count(),
            'total_palettes' => Palette::whereYear('created_at', $year)->count(),
        ];
        StatsHistory::updateOrCreate(['year' => $year], ['data' => $data]);
        return response()->json(['message' => "Year $year archived."]);
    });

    // Stats history
    Route::get('/stats/history', function () {
        return StatsHistory::orderByDesc('year')->get();
    });

    // Staff list
    Route::get('/staff', function () {
        return User::whereIn('role', ['admin', 'superadmin'])
            ->withCount('palettes')
            ->get();
    });

    // Get all appeals
    Route::get('/appeals', function (Request $request) {
        $status = $request->query('status', 'pending');
        $search = $request->query('search', '');

        return Appeal::with(['user:id,name,avatar', 'warning', 'images', 'reviewer:id,name'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) =>
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
            )
            ->latest()
            ->paginate(20);
    });

    // Review appeal
    Route::patch('/appeals/{appeal}/review', function (Request $request, Appeal $appeal) {
        $request->validate([
            'decision'       => 'required|in:accept,reject',
            'admin_response' => 'nullable|string|max:500',
        ]);

        $appeal->status         = $request->decision === 'accept' ? 'accepted' : 'rejected';
        $appeal->admin_response = $request->admin_response;
        $appeal->reviewed_by    = $request->user()->id;
        $appeal->reviewed_at    = now();
        $appeal->save();

        $user = $appeal->warning->user;

        if ($request->decision === 'reject') {
            if ($user->strikes_reset_at && now()->gt($user->strikes_reset_at)) {
                $user->strikes = 0;
            }
            $user->strikes += 1;
            $user->strikes_reset_at = $user->strikes_reset_at ?? now()->addYear();

            $banDays = match (true) {
                $user->strikes >= 15 => 365,
                $user->strikes >= 10 => 30,
                $user->strikes >= 5  => 7,
                $user->strikes >= 3  => 1,
                default              => null,
            };

            if ($banDays) {
                $user->is_banned = true;
                UserNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'warning',
                    'title'   => '🚫 Appeal Rejected — Account Banned',
                    'message' => "Your appeal was rejected. You have been banned for {$banDays} day(s) due to {$user->strikes} strikes.",
                    'data'    => ['strikes' => $user->strikes, 'ban_days' => $banDays],
                ]);
            } else {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'warning',
                    'title'   => '⚠️ Appeal Rejected — Strike Added',
                    'message' => "Your appeal was rejected. You now have {$user->strikes} strike(s). At 3 strikes you will receive a 1-day ban.",
                    'data'    => ['strikes' => $user->strikes],
                ]);
            }
            $user->save();
        } else {
            UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'general',
                'title'   => '✅ Appeal Accepted',
                'message' => 'Your appeal has been reviewed and accepted. No further action will be taken.',
                'data'    => ['admin_response' => $request->admin_response],
            ]);
            $appeal->warning->status = 'reviewed';
            $appeal->warning->save();
        }

        return response()->json(['message' => 'Appeal reviewed.', 'appeal' => $appeal]);
    });

});

/*
|--------------------------------------------------------------------------
| Super Admin Only
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'isSuperAdmin'])->prefix('admin')->group(function () {

    Route::patch('/users/{user}/role', function (Request $request, User $user) {
        $request->validate(['role' => 'required|in:user,admin,superadmin']);
        $oldRole    = $user->role;
        $user->role = $request->role;
        $user->save();

        $roleLabels = ['user' => 'User', 'admin' => 'Admin', 'superadmin' => 'Super Admin'];

        UserNotification::create([
            'user_id' => $user->id,
            'type'    => 'role_change',
            'title'   => '🎉 Your role has been updated',
            'message' => "Your role has been changed from {$roleLabels[$oldRole]} to {$roleLabels[$request->role]}.",
            'data'    => ['old_role' => $oldRole, 'new_role' => $request->role],
        ]);

        return response()->json(['message' => 'Role updated.', 'user' => $user]);
    });

});