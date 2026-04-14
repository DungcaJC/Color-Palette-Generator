<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Palette;

// ─── Auth ────────────────────────────────────────────
Route::post('/register', function (Request $request) {
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users',
        'password' => 'required|min:6|confirmed',
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json(['user' => $user, 'token' => $token], 201);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json(['user' => $user, 'token' => $token]);
});

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');

Route::get('/me', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ─── Colormind ───────────────────────────────────────
Route::post('/palette', function (Request $request) {
    $response = Http::post('http://colormind.io/api/', $request->all());
    return $response->json();
});

// ─── ColorMagic ──────────────────────────────────────
Route::get('/palette/search', function (Request $request) {
    $query = $request->query('q', 'random');
    $response = Http::get("https://colormagic.app/api/palette/search?q={$query}");
    return $response->json();
});

// ─── Saved Palettes (protected) ──────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/palettes', function (Request $request) {
        return $request->user()->palettes()->latest()->get();
    });

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

    Route::delete('/palettes/{palette}', function (Request $request, Palette $palette) {
        if ($palette->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $palette->delete();
        return response()->json(['message' => 'Deleted']);
    });
});