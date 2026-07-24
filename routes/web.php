<?php

use Illuminate\Support\Facades\Route;

// Fallback login route for Sanctum redirect
Route::get('/login', function () {
    return response()->json([
        'status' => false,
        'message' => 'Unauthenticated.',
        'errors' => [],
    ], 401);
})->name('login');

// SPA catch-all route
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
