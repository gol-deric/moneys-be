<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Only check for POST requests (creating new subscription)
        if ($request->isMethod('post')) {
            $tier = $user->subscription_tier;
            $limits = config("tier_limits.{$tier}");

            // Check subscription limit
            if ($limits['max_subscriptions'] !== null) {
                $currentCount = $user->subscriptions()->count();

                if ($currentCount >= $limits['max_subscriptions']) {
                    return response()->json([
                        'error' => 'Subscription limit reached',
                        'message' => "You have reached the maximum limit of {$limits['max_subscriptions']} subscriptions for the Free plan. Upgrade to PRO for unlimited subscriptions.",
                        'current_tier' => $tier,
                        'current_count' => $currentCount,
                        'limit' => $limits['max_subscriptions'],
                        'upgrade_required' => true,
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
