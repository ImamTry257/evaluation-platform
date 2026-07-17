<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\PeriodeController;
use App\Http\Controllers\Api\Admin\KuesionerController;
use App\Http\Controllers\Api\Admin\KomponenController;
use App\Http\Controllers\Api\Admin\SubKomponenController;
use App\Http\Controllers\Api\Admin\IndikatorController;
use App\Http\Controllers\Api\Admin\PertanyaanController;
use App\Http\Controllers\Api\Admin\RespondenController;
use App\Http\Controllers\Api\Admin\RekomendasiController;
use App\Http\Controllers\Api\Respondent\EvaluasiController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register'])
        ->middleware('throttle:100,15');
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:500,15');

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

            // Components
            Route::apiResource('components', KomponenController::class);

            // Sub-Components
            Route::apiResource('sub-components', SubKomponenController::class);

            // Indicators
            Route::apiResource('indicators', IndikatorController::class);

            // Questions
            Route::apiResource('questions', PertanyaanController::class);

            // Respondents
            Route::apiResource('respondents', RespondenController::class);

            // Recommendations
            Route::apiResource('recommendations', RekomendasiController::class);
        });

        // Respondent routes
        Route::prefix('evaluations')->group(function () {
            Route::post('/start', [EvaluasiController::class, 'start']);
            Route::get('/{sessionId}', [EvaluasiController::class, 'show']);
            Route::post('/{sessionId}/answers', [EvaluasiController::class, 'saveAnswer']);
            Route::post('/{sessionId}/autosave', [EvaluasiController::class, 'autoSave']);
            Route::post('/{sessionId}/submit', [EvaluasiController::class, 'submit']);
            Route::get('/{sessionId}/results', [EvaluasiController::class, 'results']);
        });
    });
});
