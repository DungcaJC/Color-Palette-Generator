<?php

// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Palette;

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
});

// ─── Admin Routes ─────────────────────────────────────
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {

    // Dashboard stats
    Route::get('/stats', function () {
        return response()->json([
            'total_users'    => \App\Models\User::count(),
            'total_palettes' => \App\Models\Palette::count(),
            'by_source'      => [
                'image'   => \App\Models\Palette::where('source', 'image')->count(),
                'keyword' => \App\Models\Palette::where('source', 'keyword')->count(),
                'created' => \App\Models\Palette::where('source', 'created')->count(),
            ],
            'new_users_this_week' => \App\Models\User::where('created_at', '>=', now()->subWeek())->count(),
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
    // Inside admin group — replace existing palettes route
    Route::get('/palettes', function (Request $request) {
        $query = \App\Models\Palette::with('user:id,name,email')->latest();
        if ($request->query('source') && $request->query('source') !== 'all') {
            $query->where('source', $request->query('source'));
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