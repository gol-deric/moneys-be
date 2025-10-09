<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get subscriptions grouped by billing date for a specific month.
     */
    public function show(Request $request, int $year, int $month): JsonResponse
    {
        $user = $request->user();
        $subscriptions = $user->activeSubscriptions;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $calendar = [];

        foreach ($subscriptions as $subscription) {
            $nextBillingDate = $subscription->next_billing_date;

            // Check if the next billing date falls within the requested month
            if ($nextBillingDate->between($startOfMonth, $endOfMonth)) {
                $dayKey = $nextBillingDate->day;

                if (!isset($calendar[$dayKey])) {
                    $calendar[$dayKey] = [
                        'date' => $nextBillingDate->toDateString(),
                        'subscriptions' => [],
                    ];
                }

                $calendar[$dayKey]['subscriptions'][] = [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'icon_url' => $subscription->icon_url,
                    'price' => $subscription->price,
                    'currency_code' => $subscription->currency_code,
                    'category' => $subscription->category,
                ];
            }
        }

        // Sort by day
        ksort($calendar);

        return response()->json([
            'year' => $year,
            'month' => $month,
            'calendar' => array_values($calendar),
        ]);
    }
}
