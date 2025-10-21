# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MoneyS is an **API-only** user and device management system built with Laravel 12. This application has been refactored from a full-stack system to focus exclusively on providing RESTful API endpoints.

**Current State:**
- ✅ Pure API backend (no admin panel, no web UI)
- ✅ User authentication and registration (including guest accounts)
- ✅ Device management per user
- ✅ Subscription management per user
- ✅ L5-Swagger for API documentation
- ✅ UUID-based primary keys throughout

**Tech Stack:**
- Laravel 12
- Laravel Sanctum (API token authentication)
- L5-Swagger (OpenAPI/Swagger documentation)
- MySQL with UUID primary keys

## Development Commands

### Initial Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

**Important:** Set `API_KEY` in `.env` - all API requests require this header.

### Running Development Server
```bash
php artisan serve
# API available at: http://localhost:8000
```

### Database Operations
```bash
php artisan migrate           # Run migrations
php artisan migrate:fresh     # Reset and rebuild database
```

### API Documentation
```bash
php artisan l5-swagger:generate
# View at: http://localhost:8000/api/documentation
```

### Code Quality
```bash
php artisan pint             # Format code
php artisan test             # Run tests
```

## Architecture Overview

### API-Only Application
This is strictly an API backend. There is **no web UI or admin panel**. The root URL (`/`) returns JSON with API information.

### Data Model
The application uses the following primary tables:

**1. users**
- UUID primary key
- Supports both regular users (email/password) and guest users (no credentials)
- Fields: `email`, `password`, `full_name`, `is_guest`, `language`, `currency`, `is_active`, `last_logged_in`

**2. user_devices**
- UUID primary key and foreign key
- One user can have multiple devices
- Fields: `device_id`, `device_name`, `device_type` (android/ios/web), `fcm_token`, `is_active`

**3. subscriptions**
- UUID primary key and foreign key
- Tracks user subscriptions
- Fields: `user_id`, `name`, `description`, `price`, `currency`, `billing_cycle`, `start_date`, `end_date`, `status`

### UUID Primary Keys
All models use UUIDs instead of auto-incrementing integers:
- Models use `HasUuids` trait
- Migrations use `$table->uuid('id')->primary()`
- Foreign keys use `$table->foreignUuid('user_id')`

### Authentication Flow

**Two-Layer Authentication:**
1. **API Key** (all requests): Header `X-API-KEY` validated by `ValidateApiSecurityKey` middleware
2. **User Token** (protected routes): Sanctum bearer token for authenticated users

**Guest vs Regular Users:**
- Guest users: `is_guest=true`, no email/password required, identified by `device_id`
- Regular users: `is_guest=false`, require email/password

### Request/Response Pattern

**Base Controller** (`app/Http/Controllers/Api/Controller.php`) provides response helpers:
```php
$this->success($data, $message, $code)
$this->error($message, $code, $errors)
```

**Standard Response Format:**
```json
{
  "success": true/false,
  "message": "...",
  "data": {...} // or "errors": {...}
}
```

### Form Requests
All API inputs validated via Form Requests in `app/Http/Requests/`:
- `RegisterRequest` - validates guest vs regular registration differently
- `LoginRequest`
- `ForgotPasswordRequest`
- `ResetPasswordRequest`
- `CreateSubscriptionRequest` - validates subscription creation
- `UpdateSubscriptionRequest` - validates subscription updates

## API Endpoints

**Base:** `/api/v1`
**Required Header:** `X-API-KEY: {value-from-env}`

### Public Routes
- `POST /user/register` - Create user (guest or regular) + device
- `POST /user/login` - Authenticate with email/password
- `POST /user/forgot-password` - Request password reset
- `POST /user/reset-password` - Reset with token
- `GET /health` - Health check

### Protected Routes (require Bearer token)
- `GET /user/me` - Get authenticated user info
- `POST /user/device` - Add a new device for the user
- `DELETE /user/device/{device_id}` - Remove a device

**Subscription Management:**
- `GET /subscription` - Get all subscriptions for authenticated user
- `POST /subscription` - Create a new subscription
- `GET /subscription/{id}` - Get a specific subscription
- `PUT /subscription/{id}` - Update a subscription
- `DELETE /subscription/{id}` - Delete a subscription

## Key Configuration

### Environment Variables
Required in `.env`:
```
API_KEY=your-secret-api-key-here  # Required for all API requests
DB_DATABASE=moneys
```

Optional for password reset emails:
```
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
```

### Middleware
- `api.key` - Validates `X-API-KEY` header (applied to all `/api/v1` routes)
- `auth:sanctum` - Validates bearer token (applied to protected routes)

## Important Implementation Notes

### Password Reset
Uses Laravel's built-in password reset:
- Token stored in `password_reset_tokens` table
- Requires email configuration in `.env`
- Uses `Password` facade for token generation/validation

### Swagger Documentation
- Main API info: `app/Http/Controllers/Api/Controller.php`
- Schemas: `app/Http/Controllers/Api/Schemas.php`
- Endpoint annotations: In controller methods using `@OA\` tags
- Security schemes: Both `apiKey` (X-API-KEY) and `sanctum` (Bearer) defined

### Model Relationships
```php
User::devices()           // HasMany UserDevice
User::subscriptions()     // HasMany Subscription
UserDevice::user()        // BelongsTo User
Subscription::user()      // BelongsTo User
```

### Soft Deletes
User model uses soft deletes - deleted users aren't immediately removed from database.

## Migrations Structure

Current migrations (in order):
1. `create_users_table` - Base Laravel users
2. `create_cache_table` - Laravel cache
3. `create_jobs_table` - Laravel queue jobs
4. `create_personal_access_tokens_table` - Sanctum tokens
5. `update_personal_access_tokens_for_uuid` - Convert to UUID foreign keys
6. `create_users_and_devices_tables_v2` - Main schema (modifies users, creates user_devices)
7. `drop_unused_tables` - Cleanup from previous full-stack version
8. `create_subscriptions_table` - Subscription management
9. `create_admin_users_table` - Admin users (if Filament is still in use)

## What Was Removed

This codebase was refactored from a full-stack application. The following were removed or are being removed:
- ⚠️ Filament admin panel (partially removed - some resources still exist)
- ❌ Firebase Cloud Messaging service
- ❌ Payment plans and billing
- ❌ Notification system
- ❌ Background jobs and scheduled tasks
- ❌ Laravel Horizon

**Note:** Subscription management is **ACTIVE** - the API endpoints and database tables exist and are functional. The README.md may mention removed features that no longer exist.

## Testing

PHPUnit configured for Laravel 12. Test files go in:
- `tests/Feature/` - Integration tests
- `tests/Unit/` - Unit tests
