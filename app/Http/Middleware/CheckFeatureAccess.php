<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature  Feature key to check (e.g., 'export_data', 'advanced_reports')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to access this feature.',
            ], 401);
        }

        $tier = $user->subscription_tier;
        $limits = config("tier_limits.{$tier}");

        // Check feature access based on tier
        $hasAccess = match ($feature) {
            'export_data' => $limits['can_export'] ?? false,
            'advanced_reports' => $limits['can_view_reports'] ?? false,
            'custom_notifications' => $limits['can_customize_notifications'] ?? false,
            default => false,
        };

        if (!$hasAccess) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "This feature is only available for PRO users. Please upgrade your plan.",
                'feature' => $feature,
                'current_tier' => $tier,
                'upgrade_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
