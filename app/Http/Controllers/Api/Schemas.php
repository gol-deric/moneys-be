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
 *
 * @OA\Schema(
 *     schema="Subscription",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="9d4e8f72-8b7a-4c5e-a3d2-1f9b8c6d5e4a"),
 *     @OA\Property(property="user_id", type="string", format="uuid", example="9d4e8f72-8b7a-4c5e-a3d2-1f9b8c6d5e4a"),
 *     @OA\Property(property="name", type="string", example="Netflix Premium"),
 *     @OA\Property(property="description", type="string", example="Monthly streaming subscription", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=15.99),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="billing_cycle", type="string", enum={"monthly", "yearly", "weekly", "daily"}, example="monthly"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2025-12-31", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"active", "cancelled", "expired", "pending"}, example="active"),
 *     @OA\Property(property="app_icon", type="string", example="https://moneys.io.vn/assets/icons/netflix.png", nullable=true, description="URL or path to app icon from subscriptions.json"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="CreateSubscriptionRequest",
 *     type="object",
 *     required={"name", "price", "billing_cycle", "start_date"},
 *     @OA\Property(property="name", type="string", example="Netflix Premium"),
 *     @OA\Property(property="description", type="string", example="Monthly streaming subscription", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=15.99),
 *     @OA\Property(property="currency", type="string", example="USD", nullable=true),
 *     @OA\Property(property="billing_cycle", type="string", enum={"monthly", "yearly", "weekly", "daily"}, example="monthly"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2025-12-31", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"active", "cancelled", "expired", "pending"}, example="active", nullable=true),
 *     @OA\Property(property="app_icon", type="string", example="https://moneys.io.vn/assets/icons/netflix.png", nullable=true, description="URL or path to app icon")
 * )
 *
 * @OA\Schema(
 *     schema="UpdateSubscriptionRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", example="Netflix Premium", nullable=true),
 *     @OA\Property(property="description", type="string", example="Monthly streaming subscription", nullable=true),
 *     @OA\Property(property="price", type="number", format="float", example=15.99, nullable=true),
 *     @OA\Property(property="currency", type="string", example="USD", nullable=true),
 *     @OA\Property(property="billing_cycle", type="string", enum={"monthly", "yearly", "weekly", "daily"}, example="monthly", nullable=true),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-01-01", nullable=true),
 *     @OA\Property(property="end_date", type="string", format="date", example="2025-12-31", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"active", "cancelled", "expired", "pending"}, example="active", nullable=true),
 *     @OA\Property(property="app_icon", type="string", example="https://moneys.io.vn/assets/icons/netflix.png", nullable=true, description="URL or path to app icon")
 * )
 */
class Schemas
{
    // This class only contains OpenAPI schema definitions
}
