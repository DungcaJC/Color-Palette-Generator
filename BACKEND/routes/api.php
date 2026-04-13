<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use Illuminate\Support\Facades\Http;

Route::post('/palette', function (Request $request) {
    $response = Http::post('http://colormind.io/api/', $request->all());
    return $response->json();
});

Route::get('/palette/models', function () {
    $response = Http::get('http://colormind.io/list/');
    return $response->json();
});

Route::get('/palette/search', function (Request $request) {
    $query = $request->query('q', 'random');
    $response = Http::get("https://colormagic.app/api/palette/search?q={$query}");
    return $response->json();
});
