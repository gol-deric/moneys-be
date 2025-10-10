<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_AndroidPublisher;

class GooglePlayBillingController extends Controller
{
    /**
     * Verify and process Google Play purchase.
     */
    public function verifyPurchase(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|string',
            'purchase_token' => 'required|string',
            'order_id' => 'required|string',
            'purchase_time' => 'required|integer', // timestamp in milliseconds
            'receipt_data' => 'sometimes|array',
        ]);

        $user = $request->user();

        // Check if purchase already exists
        $existingPurchase = Purchase::where('order_id', $request->order_id)->first();
        if ($existingPurchase) {
            return response()->json([
                'error' => 'Purchase already processed',
                'message' => 'This purchase has already been verified.',
                'purchase' => $existingPurchase,
            ], 400);
        }

        try {
            // Verify with Google Play API
            $verificationResult = $this->verifyWithGooglePlay(
                $request->product_id,
                $request->purchase_token
            );

            if (!$verificationResult['valid']) {
                return response()->json([
                    'error' => 'Invalid purchase',
                    'message' => 'Could not verify purchase with Google Play.',
                ], 400);
            }

            // Create purchase record
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'order_id' => $request->order_id,
                'purchase_token' => $request->purchase_token,
                'receipt_data' => json_encode($request->receipt_data ?? []),
                'platform' => 'google_play',
                'purchase_type' => 'subscription',
                'amount' => $this->getProductPrice($request->product_id),
                'currency' => 'USD',
                'purchased_at' => now()->setTimestampMs($request->purchase_time),
                'expires_at' => $verificationResult['expires_at'] ?? now()->addYear(),
                'auto_renewing' => $verificationResult['auto_renewing'] ?? true,
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            // Upgrade user to PRO
            $user->update([
                'subscription_tier' => 'pro',
                'subscription_expires_at' => $purchase->expires_at,
            ]);

            return response()->json([
                'message' => 'Purchase verified successfully',
                'purchase' => $purchase,
                'user' => $user->fresh(),
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Google Play purchase verification failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'order_id' => $request->order_id,
            ]);

            return response()->json([
                'error' => 'Verification failed',
                'message' => 'Failed to verify purchase. Please try again.',
            ], 500);
        }
    }

    /**
     * Get user's purchase history.
     */
    public function purchaseHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $purchases = $user->purchases()
            ->orderBy('purchased_at', 'desc')
            ->get();

        return response()->json($purchases);
    }

    /**
     * Get active subscription.
     */
    public function activeSubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $activePurchase = $user->purchases()
            ->where('status', 'verified')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('expires_at', 'desc')
            ->first();

        if (!$activePurchase) {
            return response()->json([
                'message' => 'No active subscription',
                'has_active' => false,
            ]);
        }

        return response()->json([
            'has_active' => true,
            'subscription' => $activePurchase,
        ]);
    }

    /**
     * Verify purchase with Google Play Billing API.
     */
    private function verifyWithGooglePlay(string $productId, string $purchaseToken): array
    {
        // Check if Google Play credentials are configured
        $credentialsPath = env('GOOGLE_PLAY_CREDENTIALS_PATH');

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            // For development: skip actual verification
            \Log::warning('Google Play credentials not configured. Skipping verification.');

            return [
                'valid' => true,
                'expires_at' => now()->addYear(),
                'auto_renewing' => true,
            ];
        }

        try {
            // Initialize Google Client
            $client = new Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Google_Service_AndroidPublisher::ANDROIDPUBLISHER);

            $service = new Google_Service_AndroidPublisher($client);
            $packageName = env('GOOGLE_PLAY_PACKAGE_NAME', 'com.moneys.app');

            // Get subscription info
            $subscription = $service->purchases_subscriptionsv2->get(
                $packageName,
                $purchaseToken
            );

            // Check if subscription is valid
            $isValid = in_array($subscription->getSubscriptionState(), [
                'SUBSCRIPTION_STATE_ACTIVE',
                'SUBSCRIPTION_STATE_IN_GRACE_PERIOD',
            ]);

            if (!$isValid) {
                return ['valid' => false];
            }

            // Get expiry time
            $expiryTime = $subscription->getLineItems()[0]->getExpiryTime();
            $expiresAt = $expiryTime ? now()->setTimestampMs($expiryTime / 1000) : now()->addYear();

            return [
                'valid' => true,
                'expires_at' => $expiresAt,
                'auto_renewing' => $subscription->getAutoRenewing() ?? true,
            ];

        } catch (\Exception $e) {
            \Log::error('Google Play API verification error', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);

            throw $e;
        }
    }

    /**
     * Get product price by product ID.
     */
    private function getProductPrice(string $productId): float
    {
        $prices = [
            'moneys_pro_yearly' => 10.00,
            'moneys_pro_monthly' => 1.00,
        ];

        return $prices[$productId] ?? 10.00;
    }
}
