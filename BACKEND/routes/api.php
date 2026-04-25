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

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Register - ✅ now returns a token so the user is immediately authenticated
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

    // ✅ Create token immediately on register so frontend can use API right away
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

    // ← add this
    if ($user->is_banned) {
        return response()->json(['message' => 'Your account has been banned.'], 403);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token'   => $token,
        'user'    => $user,
    ]);
});

// Palette search proxy (public - no auth needed)
Route::get('/palette/search', function (Request $request) {
    $q = $request->query('q', '');
    $response = \Illuminate\Support\Facades\Http::get('https://colormagic.app/api/palette/search', [
        'q' => $q,
    ]);
    return response()->json($response->json());
});

// Colormind proxy (this is the real one)
Route::post('/palette', function (Request $request) {
    $response = \Illuminate\Support\Facades\Http::post('http://colormind.io/api/', $request->all());
    return response()->json($response->json());
});

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

    // Image required for creation, optional for palette
    if ($postType === 'creation' && !$request->hasFile('image')) {
        return response()->json(['message' => 'Image is required for creation posts.'], 422);
    }

    $path = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('posts', 'public');
    }

    $post = \App\Models\Post::create([
        'user_id'   => $request->user()->id,
        'image'     => $path,
        'caption'   => $request->caption,
        'colors'    => $request->colors ?? [],
        'category'  => $category,
        'post_type' => $postType,
    ]);

    return response()->json($post->load('user:id,name,avatar'), 201);
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

    // ✅ "Clear all" route MUST come before the wildcard {palette} route to avoid conflict
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

    // Delete single palette — ✅ comes AFTER /palettes/all
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

    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
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
    Route::post('/posts/{post}/save', function (Request $request, \App\Models\Post $post) {
        $existing = \App\Models\SavedPost::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['saved' => false]);
        }
        \App\Models\SavedPost::create(['post_id' => $post->id, 'user_id' => $request->user()->id]);
        return response()->json(['saved' => true]);
    });

    // Get saved posts
    Route::get('/saved-posts', function (Request $request) {
        $savedPostIds = \App\Models\SavedPost::where('user_id', $request->user()->id)->pluck('post_id');
        return \App\Models\Post::whereIn('id', $savedPostIds)
            ->with('user:id,name,avatar')
            ->withCount(['likes as liked_by_user' => function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            }])
            ->latest()->get();
    });

    // Get public user profile
    Route::get('/users/{user}/profile', function (\App\Models\User $user, Request $request) {
        $posts = \App\Models\Post::where('user_id', $user->id)
            ->with('user:id,name,avatar')
            ->withCount(['likes as liked_by_user' => function ($q) use ($request) {
                $q->where('user_id', $request->user()?->id ?? 0);
            }])
            ->latest()->get();

        $palettes = \App\Models\Palette::where('user_id', $user->id)->latest()->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ],
            'posts' => $posts,
            'palettes' => $palettes,
        ]);
    });
});

// ─── Admin Routes ─────────────────────────────────────
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

    // Dashboard stats
    Route::get('/stats', function () {
        return response()->json([
            'total_users'          => \App\Models\User::count(),
            'total_palettes'       => \App\Models\Palette::count(),
            'total_posts'          => Post::count(),
            'by_source'            => [
                'image'   => \App\Models\Palette::where('source', 'image')->count(),
                'keyword' => \App\Models\Palette::where('source', 'keyword')->count(),
                'created' => \App\Models\Palette::where('source', 'created')->count(),
            ],
            'new_users_this_week'  => \App\Models\User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_this_month' => \App\Models\User::where('created_at', '>=', now()->subMonth())->count(),
        ]);
    });

    // Debug route to check auth and roles
    Route::get('/debug-me', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'role' => $request->user()?->role,
            'isAdmin' => $request->user()?->isAdmin(),
        ]);
    })->middleware('auth:sanctum');

    // Get all users
    Route::get('/users', function (Request $request) {
        $search = $request->query('search', '');
        return \App\Models\User::when($search, fn($q) =>
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
        )
        ->withCount('palettes')
        ->latest()
        ->get();
    });

    // Get single user
    Route::get('/users/{user}', function (\App\Models\User $user) {
        return $user->loadCount('palettes')->load('palettes');
    });

    // Ban / Unban user (toggle)
    Route::patch('/users/{user}/ban', function (Request $request, \App\Models\User $user) {
        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Cannot ban a super admin.'], 403);
        }
        $user->is_banned = !$user->is_banned;
        $user->save();
        return response()->json(['message' => $user->is_banned ? 'User banned.' : 'User unbanned.', 'is_banned' => $user->is_banned]);
    });

    // Delete user
    Route::delete('/users/{user}', function (Request $request, \App\Models\User $user) {
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
        $query = \App\Models\Palette::with('user:id,name,email')->latest();
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
    Route::delete('/palettes/{palette}', function (\App\Models\Palette $palette) {
        $palette->delete();
        return response()->json(['message' => 'Palette deleted.']);
    });

});

// ─── Super Admin Only ─────────────────────────────────
Route::middleware(['auth:sanctum', 'isSuperAdmin'])->prefix('admin')->group(function () {

    // Promote/demote user role
    Route::patch('/users/{user}/role', function (Request $request, \App\Models\User $user) {
        $request->validate(['role' => 'required|in:user,admin,superadmin']);
        $user->role = $request->role;
        $user->save();
        return response()->json(['message' => 'Role updated.', 'user' => $user]);
    });

});

// ─── Community Routes ──────────────────────────────────

// Get posts (public feed)
Route::get('/posts', function (Request $request) {
    $category = $request->query('category', 'all');
    $sort     = $request->query('sort', 'latest');
    $search   = $request->query('search', '');
    $type     = $request->query('type', 'posts'); // posts or people
    
    if ($type === 'people') {
        $users = \App\Models\User::when($search, fn($q) =>
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
        )
        ->withCount('posts')
        ->latest()
        ->paginate(20);
        return response()->json($users);
    }

    $query = \App\Models\Post::with('user:id,name,avatar,role,bio')
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

// Protected community routes
Route::middleware('auth:sanctum')->group(function () {

    // Create post
    Route::post('/posts', function (Request $request) {
        $request->validate([
            'image'    => 'required|image|max:5120',
            'caption'  => 'nullable|string|max:500',
            'colors'   => 'nullable|array',
            'category' => 'nullable|string|max:100',
        ]);

        $path = $request->file('image')->store('posts', 'public');

        $post = \App\Models\Post::create([
            'user_id'  => $request->user()->id,
            'image'    => $path,
            'caption'  => $request->caption,
            'colors'   => $request->colors ?? [],
            'category' => $request->category ?? 'Other',
        ]);

        return response()->json($post->load('user:id,name,avatar'), 201);
    });

    // Delete own post
    Route::delete('/posts/{post}', function (Request $request, \App\Models\Post $post) {
        if ($post->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // delete image file
        \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    });

    // Like / Unlike toggle
    Route::post('/posts/{post}/like', function (Request $request, \App\Models\Post $post) {
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
        return response()->json(['liked' => true, 'likes_count' => $post->fresh()->likes_count]);
    });

    // Report post
    Route::post('/posts/{post}/report', function (Request $request, \App\Models\Post $post) {
        $request->validate([
            'topic'   => 'required|in:spam,inappropriate,harassment,copyright,other',
            'details' => 'nullable|string|max:500',
        ]);

        // prevent duplicate reports
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

});

// ─── Admin Community Routes ────────────────────────────
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

    // Get all reports
    Route::get('/reports', function (Request $request) {
        $status = $request->query('status', 'pending');
        return \App\Models\Report::with([
            'post.user:id,name,avatar',
            'reporter:id,name'
        ])
        ->when($status !== 'all', fn($q) => $q->where('status', $status))
        ->latest()
        ->paginate(20);
    });

    // Update report status
    Route::patch('/reports/{report}/status', function (Request $request, \App\Models\Report $report) {
        $request->validate(['status' => 'required|in:pending,reviewed,dismissed']);
        $report->status = $request->status;
        $report->save();
        return response()->json(['message' => 'Report updated.']);
    });

    // Admin delete any post
    Route::delete('/posts/{post}', function (\App\Models\Post $post) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
        $post->delete();
        return response()->json(['message' => 'Post deleted by admin.']);
    });

});

// ─── User Notifications (protected) ───────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Get user's server notifications
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

    // Mark all as read
    Route::patch('/notifications/read-all', function (Request $request) {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
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

});

// ─── Admin Warning + Stats routes ─────────────────────
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

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

        // Send notification to user
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

    // Get daily stats for dashboard chart
    Route::get('/stats/daily', function () {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'date'     => $date->format('M d'),
                'users'    => \App\Models\User::whereDate('created_at', $date)->count(),
                'posts'    => Post::whereDate('created_at', $date)->count(),
                'palettes' => \App\Models\Palette::whereDate('created_at', $date)->count(),
            ];
        });
        return response()->json($days);
    });

    Route::get('/stats/monthly', function () {
        $months = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month'    => $date->format('M Y'),
                'users'    => \App\Models\User::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'posts'    => Post::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'palettes' => \App\Models\Palette::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        });
        return response()->json($months);
    });

    // Archive yearly stats
    Route::post('/stats/archive', function () {
        $year = now()->subYear()->year;
        $data = [
            'total_users'    => \App\Models\User::whereYear('created_at', $year)->count(),
            'total_posts'    => Post::whereYear('created_at', $year)->count(),
            'total_palettes' => \App\Models\Palette::whereYear('created_at', $year)->count(),
        ];
        StatsHistory::updateOrCreate(['year' => $year], ['data' => $data]);
        return response()->json(['message' => "Year $year archived."]);
    });

    Route::get('/stats/history', function () {
        return StatsHistory::orderByDesc('year')->get();
    });

    // Get staff list (admins + superadmins)
    Route::get('/staff', function () {
        return \App\Models\User::whereIn('role', ['admin', 'superadmin'])
            ->withCount('palettes')
            ->get();
    });

});

// Role change — super admin only
Route::middleware(['auth:sanctum', 'isSuperAdmin'])->prefix('admin')->group(function () {
    Route::patch('/users/{user}/role', function (Request $request, \App\Models\User $user) {
        $request->validate(['role' => 'required|in:user,admin,superadmin']);
        $oldRole   = $user->role;
        $user->role = $request->role;
        $user->save();

        $roleLabels = ['user' => 'User', 'admin' => 'Admin', 'superadmin' => 'Super Admin'];

        // Notify the user of role change
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