<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/guest', [AuthController::class, 'guest']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/upgrade-guest', [AuthController::class, 'upgradeGuest']);

        // User routes
        Route::prefix('users')->group(function () {
            Route::get('/me', [UserController::class, 'me']);
            Route::patch('/me', [UserController::class, 'update']);
            Route::delete('/me', [UserController::class, 'destroy']);
            Route::post('/fcm-token', [UserController::class, 'updateFcmToken']);
        });

        // Subscription routes
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index']);
            Route::post('/', [SubscriptionController::class, 'store']);
            Route::get('/stats', [SubscriptionController::class, 'stats']);
            Route::get('/{id}', [SubscriptionController::class, 'show']);
            Route::patch('/{id}', [SubscriptionController::class, 'update']);
            Route::delete('/{id}', [SubscriptionController::class, 'destroy']);
            Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel']);
        });

        // Calendar routes
        Route::get('/calendar/{year}/{month}', [CalendarController::class, 'show']);

        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        });
    });
});
