<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
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
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to access this resource.',
            ], 401);
        }

        if (!$user->is_admin) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource. Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
