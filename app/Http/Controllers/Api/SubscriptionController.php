<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * List all subscriptions with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->subscriptions();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_cancelled')) {
            $query->where('is_cancelled', $request->boolean('is_cancelled'));
        }

        $subscriptions = $query->paginate($request->get('per_page', 15));

        return response()->json($subscriptions);
    }

    /**
     * Create a new subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon_url' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'currency_code' => 'required|string|size:3',
            'start_date' => 'required|date',
            'billing_cycle_count' => 'required|integer|min:1',
            'billing_cycle_period' => 'required|in:day,month,quarter,year',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $subscription = $request->user()->subscriptions()->create($request->all());

        return response()->json($subscription, 201);
    }

    /**
     * Get subscription statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscriptions = $user->activeSubscriptions;

        $totalMonthly = 0;
        $now = Carbon::now();

        foreach ($subscriptions as $subscription) {
            $monthlyPrice = match ($subscription->billing_cycle_period) {
                'day' => $subscription->price * 30 / $subscription->billing_cycle_count,
                'month' => $subscription->price / $subscription->billing_cycle_count,
                'quarter' => $subscription->price / ($subscription->billing_cycle_count * 3),
                'year' => $subscription->price / ($subscription->billing_cycle_count * 12),
                default => 0,
            };

            $totalMonthly += $monthlyPrice;
        }

        $currentDay = $now->day;
        $daysInMonth = $now->daysInMonth;
        $daysRemaining = $daysInMonth - $currentDay;

        $paidAmount = ($totalMonthly / $daysInMonth) * $currentDay;
        $remainingAmount = ($totalMonthly / $daysInMonth) * $daysRemaining;

        return response()->json([
            'total_monthly_cost' => round($totalMonthly, 2),
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => round($remainingAmount, 2),
            'subscription_count' => $subscriptions->count(),
        ]);
    }

    /**
     * Get a single subscription.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);

        return response()->json($subscription);
    }

    /**
     * Update a subscription.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon_url' => 'sometimes|nullable|string|max:500',
            'price' => 'sometimes|numeric|min:0',
            'currency_code' => 'sometimes|string|size:3',
            'start_date' => 'sometimes|date',
            'billing_cycle_count' => 'sometimes|integer|min:1',
            'billing_cycle_period' => 'sometimes|in:day,month,quarter,year',
            'category' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',
        ]);

        $subscription->update($request->all());

        return response()->json($subscription->fresh());
    }

    /**
     * Delete a subscription (soft delete).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);
        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $subscription = $request->user()->subscriptions()->findOrFail($id);

        $subscription->update([
            'is_cancelled' => true,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription->fresh(),
        ]);
    }
}
