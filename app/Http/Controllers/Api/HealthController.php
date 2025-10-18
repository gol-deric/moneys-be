<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/health",
     *     summary="Health check endpoint",
     *     description="Check if the API is running",
     *     tags={"System"},
     *     security={{"apiKey": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="API is running"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", example="healthy"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-18T10:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid or missing API key",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or missing API key")
     *         )
     *     )
     * )
     */
    public function check(): JsonResponse
    {
        return $this->success([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ], 'API is running');
    }
}
