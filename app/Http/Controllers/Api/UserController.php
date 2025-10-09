<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Update authenticated user.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'avatar_url' => 'sometimes|nullable|string|max:500',
            'locale' => 'sometimes|string|max:10',
            'currency_code' => 'sometimes|string|size:3',
            'theme' => 'sometimes|in:light,dark',
            'notifications_enabled' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
        ]);

        $user->update($request->only([
            'full_name',
            'avatar_url',
            'locale',
            'currency_code',
            'theme',
            'notifications_enabled',
            'email_notifications',
        ]));

        return response()->json($user->fresh());
    }

    /**
     * Delete authenticated user (soft delete).
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }

    /**
     * Update FCM token.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'message' => 'FCM token updated successfully',
        ]);
    }
}
