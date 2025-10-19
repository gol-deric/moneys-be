<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('api.key')->group(function () {
    // Health check
    Route::get('/health', [HealthController::class, 'check']);

    // Authentication routes (public)
    Route::prefix('user')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user/me', [AuthController::class, 'me']);

        // Device management routes
        Route::post('/user/device', [AuthController::class, 'addDevice']);
        Route::delete('/user/device/{device_id}', [AuthController::class, 'removeDevice']);

        // Subscription routes
        Route::get('/subscription', [SubscriptionController::class, 'index']);
        Route::post('/subscription', [SubscriptionController::class, 'store']);
        Route::get('/subscription/{id}', [SubscriptionController::class, 'show']);
        Route::put('/subscription/{id}', [SubscriptionController::class, 'update']);
        Route::delete('/subscription/{id}', [SubscriptionController::class, 'destroy']);
    });
});
