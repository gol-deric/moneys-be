<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiSecurityKey
{
    /**
     * Handle an incoming request.
     * Validates the X-API-KEY header against the configured API_KEY.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key');
        $expectedKey = config('app.api_security_key');

        // API key must be configured
        if (empty($expectedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not configured on server',
            ], 500);
        }
        
        // Validate the API key
        if (empty($apiKey) || $apiKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key',
            ], 401);
        }

        return $next($request);
    }
}
