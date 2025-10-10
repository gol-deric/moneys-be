<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DeviceToken;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationManagementController extends Controller
{
    public function __construct(
        private FirebaseService $firebaseService
    ) {}

    /**
     * Send notification to specific user (admin only).
     */
    public function sendToUser(Request $request, string $userId): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'sometimes|array',
        ]);

        $user = User::findOrFail($userId);

        // Get all active device tokens for user
        $deviceTokens = $user->deviceTokens()->active()->get();

        if ($deviceTokens->isEmpty()) {
            return response()->json([
                'error' => 'No devices',
                'message' => 'User has no active device tokens.',
            ], 400);
        }

        $successCount = 0;
        $failedTokens = [];

        foreach ($deviceTokens as $deviceToken) {
            try {
                $this->firebaseService->sendNotification(
                    $deviceToken->fcm_token,
                    $request->title,
                    $request->body,
                    $request->data ?? []
                );

                $deviceToken->markAsUsed();
                $successCount++;
            } catch (\Exception $e) {
                $failedTokens[] = [
                    'token_id' => $deviceToken->id,
                    'error' => $e->getMessage(),
                ];

                // Mark token as inactive if send failed
                $deviceToken->deactivate();
            }
        }

        return response()->json([
            'message' => 'Notifications sent',
            'total_devices' => $deviceTokens->count(),
            'success_count' => $successCount,
            'failed_count' => count($failedTokens),
            'failed_tokens' => $failedTokens,
        ]);
    }

    /**
     * Send notification to all users (admin only).
     */
    public function sendToAll(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'sometimes|array',
        ]);

        // Get all active device tokens
        $deviceTokens = DeviceToken::active()->get();

        if ($deviceTokens->isEmpty()) {
            return response()->json([
                'error' => 'No devices',
                'message' => 'No active device tokens found.',
            ], 400);
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($deviceTokens as $deviceToken) {
            try {
                $this->firebaseService->sendNotification(
                    $deviceToken->fcm_token,
                    $request->title,
                    $request->body,
                    $request->data ?? []
                );

                $deviceToken->markAsUsed();
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $deviceToken->deactivate();
            }
        }

        return response()->json([
            'message' => 'Broadcast notification sent',
            'total_devices' => $deviceTokens->count(),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);
    }

    /**
     * Send notification to specific users (admin only).
     */
    public function sendToUsers(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|uuid|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'sometimes|array',
        ]);

        $deviceTokens = DeviceToken::active()
            ->whereIn('user_id', $request->user_ids)
            ->get();

        if ($deviceTokens->isEmpty()) {
            return response()->json([
                'error' => 'No devices',
                'message' => 'Selected users have no active device tokens.',
            ], 400);
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($deviceTokens as $deviceToken) {
            try {
                $this->firebaseService->sendNotification(
                    $deviceToken->fcm_token,
                    $request->title,
                    $request->body,
                    $request->data ?? []
                );

                $deviceToken->markAsUsed();
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $deviceToken->deactivate();
            }
        }

        return response()->json([
            'message' => 'Notifications sent to selected users',
            'selected_users' => count($request->user_ids),
            'total_devices' => $deviceTokens->count(),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);
    }
}
