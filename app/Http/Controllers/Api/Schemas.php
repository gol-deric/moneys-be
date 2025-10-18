<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="9d4e8f72-8b7a-4c5e-a3d2-1f9b8c6d5e4a"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com", nullable=true),
 *     @OA\Property(property="full_name", type="string", example="John Doe"),
 *     @OA\Property(property="is_guest", type="boolean", example=false),
 *     @OA\Property(property="language", type="string", example="en"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="last_logged_in", type="string", format="date-time", example="2025-10-18T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="UserDevice",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="user_id", type="string", format="uuid"),
 *     @OA\Property(property="device_id", type="string", example="device-uuid-123"),
 *     @OA\Property(property="device_name", type="string", example="Samsung Galaxy S21"),
 *     @OA\Property(property="device_type", type="string", example="android"),
 *     @OA\Property(property="fcm_token", type="string", example="fcm-token-123"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Schemas
{
    // This class only contains OpenAPI schema definitions
}
