<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Register FCM token for user's device.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'sometimes|string|in:android,ios,web',
            'device_name' => 'sometimes|string|max:255',
            'app_version' => 'sometimes|string|max:50',
        ]);

        $user = $request->user();

        // Check if token already exists for this user
        $existingToken = DeviceToken::where('fcm_token', $request->fcm_token)->first();

        if ($existingToken) {
            // If token belongs to another user, reassign it
            if ($existingToken->user_id !== $user->id) {
                $existingToken->update([
                    'user_id' => $user->id,
                    'device_type' => $request->device_type ?? $existingToken->device_type,
                    'device_name' => $request->device_name ?? $existingToken->device_name,
                    'app_version' => $request->app_version ?? $existingToken->app_version,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'message' => 'FCM token reassigned successfully',
                    'device_token' => $existingToken->fresh(),
                ]);
            }

            // Token already exists for this user - update it
            $existingToken->update([
                'device_type' => $request->device_type ?? $existingToken->device_type,
                'device_name' => $request->device_name ?? $existingToken->device_name,
                'app_version' => $request->app_version ?? $existingToken->app_version,
                'is_active' => true,
                'last_used_at' => now(),
            ]);

            return response()->json([
                'message' => 'FCM token updated successfully',
                'device_token' => $existingToken->fresh(),
            ]);
        }

        // Create new token
        $deviceToken = DeviceToken::create([
            'user_id' => $user->id,
            'fcm_token' => $request->fcm_token,
            'device_type' => $request->device_type,
            'device_name' => $request->device_name,
            'app_version' => $request->app_version,
            'is_active' => true,
            'last_used_at' => now(),
        ]);

        return response()->json([
            'message' => 'FCM token registered successfully',
            'device_token' => $deviceToken,
        ], 201);
    }

    /**
     * Get all device tokens for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $user->deviceTokens()
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json($tokens);
    }

    /**
     * Delete/unregister a device token.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $deviceToken = DeviceToken::findOrFail($id);

        // Check ownership
        if ($deviceToken->user_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not own this device token.',
            ], 403);
        }

        $deviceToken->delete();

        return response()->json([
            'message' => 'Device token deleted successfully',
        ]);
    }

    /**
     * Delete device token by FCM token string.
     */
    public function destroyByToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $deviceToken = DeviceToken::where('fcm_token', $request->fcm_token)
            ->where('user_id', $user->id)
            ->first();

        if (!$deviceToken) {
            return response()->json([
                'error' => 'Not found',
                'message' => 'Device token not found.',
            ], 404);
        }

        $deviceToken->delete();

        return response()->json([
            'message' => 'Device token deleted successfully',
        ]);
    }

    /**
     * Deactivate a device token (soft delete - keeps record but marks inactive).
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $deviceToken = DeviceToken::findOrFail($id);

        // Check ownership
        if ($deviceToken->user_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not own this device token.',
            ], 403);
        }

        $deviceToken->deactivate();

        return response()->json([
            'message' => 'Device token deactivated successfully',
            'device_token' => $deviceToken->fresh(),
        ]);
    }
}
