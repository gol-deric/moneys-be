<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiSecurityKey
{
    /**
     * Handle an incoming request.
     * Validates the X-API-Key header against the configured API_SECURITY_KEY.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        $expectedKey = config('app.api_security_key');

        // If no API key is configured, skip validation (for development)
        if (empty($expectedKey)) {
            return $next($request);
        }

        // Validate the API key
        if ($apiKey !== $expectedKey) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
