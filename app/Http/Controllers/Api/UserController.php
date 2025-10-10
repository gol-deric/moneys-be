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
            'language' => 'sometimes|string|max:10',
            'currency' => 'sometimes|string|size:3',
            'theme' => 'sometimes|in:light,dark',
            'notifications_enabled' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
        ]);

        $user->update($request->only([
            'full_name',
            'avatar_url',
            'language',
            'currency',
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

    /**
     * Get user tier information and limits.
     */
    public function tierInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        $tier = $user->subscription_tier;
        $limits = config("tier_limits.{$tier}");
        $currentCount = $user->subscriptions()->count();

        return response()->json([
            'current_tier' => $tier,
            'limits' => $limits,
            'usage' => [
                'subscriptions' => [
                    'current' => $currentCount,
                    'max' => $limits['max_subscriptions'] ?? 'unlimited',
                    'percentage' => $limits['max_subscriptions']
                        ? round(($currentCount / $limits['max_subscriptions']) * 100, 2)
                        : 0,
                ],
            ],
            'can_upgrade' => $tier === 'free',
            'subscription_expires_at' => $user->subscription_expires_at,
        ]);
    }

    /**
     * Upgrade user to PRO tier.
     */
    public function upgradeToPro(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->subscription_tier === 'pro') {
            return response()->json([
                'error' => 'Already on PRO tier',
                'message' => 'You are already a PRO user.',
            ], 400);
        }

        // TODO: Integrate with payment gateway
        // For now, just upgrade directly (for testing)

        $user->update([
            'subscription_tier' => 'pro',
            'subscription_expires_at' => now()->addYear(),
        ]);

        return response()->json([
            'message' => 'Successfully upgraded to PRO',
            'user' => $user->fresh(),
        ]);
    }
}
