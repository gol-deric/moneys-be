<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\GooglePlayBillingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Admin\NotificationManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('api.key')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/guest', [AuthController::class, 'guest']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Admin-only routes
        Route::prefix('admin')->middleware(\App\Http\Middleware\IsAdmin::class)->group(function () {
            Route::get('/users', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'index']);
            Route::post('/users', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'store']);
            Route::get('/users/{id}', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'show']);
            Route::patch('/users/{id}', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'update']);
            Route::delete('/users/{id}', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'destroy']);
            Route::get('/users/{id}/subscriptions', [\App\Http\Controllers\Api\Admin\UserManagementController::class, 'subscriptions']);

            // Admin notification management
            Route::post('/notifications/send-to-user/{userId}', [NotificationManagementController::class, 'sendToUser']);
            Route::post('/notifications/send-to-all', [NotificationManagementController::class, 'sendToAll']);
            Route::post('/notifications/send-to-users', [NotificationManagementController::class, 'sendToUsers']);
        });
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
            Route::get('/tier-info', [UserController::class, 'tierInfo']);
            Route::post('/upgrade-to-pro', [UserController::class, 'upgradeToPro']);
        });

        // Subscription routes
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index']);
            Route::post('/', [SubscriptionController::class, 'store'])
                ->middleware(\App\Http\Middleware\CheckSubscriptionLimit::class);
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

        // Google Play Billing routes
        Route::prefix('billing')->group(function () {
            Route::post('/google-play/verify', [GooglePlayBillingController::class, 'verifyPurchase']);
            Route::get('/purchases', [GooglePlayBillingController::class, 'purchaseHistory']);
            Route::get('/active-subscription', [GooglePlayBillingController::class, 'activeSubscription']);
        });

        // Device Token (FCM) routes
        Route::prefix('device-tokens')->group(function () {
            Route::post('/register', [DeviceTokenController::class, 'register']);
            Route::get('/', [DeviceTokenController::class, 'index']);
            Route::delete('/{id}', [DeviceTokenController::class, 'destroy']);
            Route::delete('/by-token/delete', [DeviceTokenController::class, 'destroyByToken']);
            Route::patch('/{id}/deactivate', [DeviceTokenController::class, 'deactivate']);
        });
    });
});
