<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\PeriodeController;
use App\Http\Controllers\Api\Admin\KuesionerController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,15');

    // Protected routes (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/profile', [AuthController::class, 'profile']);

        // Admin routes
        Route::prefix('admin')->middleware('admin')->group(function () {
            // Periods
            Route::apiResource('periods', PeriodeController::class);

            // Questionnaires
            Route::apiResource('questionnaires', KuesionerController::class);
        });
    });
});
