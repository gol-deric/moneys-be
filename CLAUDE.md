# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MoneyS is an **API-only** user and device management system built with Laravel 12. The system provides RESTful API endpoints for user authentication, registration, and device management.

**Tech Stack:**
- Laravel 12
- Laravel Sanctum (API authentication)
- L5-Swagger (API documentation)
- MySQL with UUID primary keys

## Development Commands

### Initial Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Running Development Server
```bash
php artisan serve
```

### Testing
```bash
php artisan test                      # Run all tests
php artisan test --filter UserTest    # Run specific test
```

### Database
```bash
php artisan migrate:fresh             # Reset database
php artisan migrate                   # Run migrations
```

### Code Quality
```bash
php artisan pint                      # Format code with Laravel Pint
```

### API Documentation
```bash
php artisan l5-swagger:generate       # Generate Swagger docs
# View at: http://localhost:8000/api/documentation
```

## Architecture & Key Patterns

### API-Only Application
This is an API-only application. There is **no admin panel or web UI**. All functionality is accessed via API endpoints.

### UUID Primary Keys
All models use UUID primary keys instead of auto-incrementing integers:
- `use HasUuids` trait in models
- `$table->uuid('id')->primary()` in migrations
- Foreign keys also use UUIDs: `$table->foreignUuid('user_id')`

### Authentication System
- **API**: Laravel Sanctum with token-based authentication
- **API Key**: All API requests require `X-API-KEY` header (configured in `.env`)
- **Guest Accounts**: Users can register as guests (`is_guest = true`) without email/password
- **Regular Accounts**: Users register with email + password

### Model Structure
Key models and their relationships:
- **User**: HasMany UserDevices
  - Fields: email, password, full_name, is_guest, language, currency, is_active, last_logged_in
- **UserDevice**: BelongsTo User
  - Fields: device_id, device_name, device_type (android/ios/web), fcm_token, is_active

### API Endpoints

**Base URL:** `/api/v1`
**Required Header:** `X-API-KEY: {your-api-key}`

**Public Routes:**
- `POST /user/register` - Register new user (guest or regular) with device
- `POST /user/login` - Login with email/password
- `POST /user/forgot-password` - Request password reset
- `POST /user/reset-password` - Reset password with token

**Protected Routes** (require Bearer token):
- `GET /user/me` - Get authenticated user info

**System:**
- `GET /health` - API health check

### Database Tables
Only 2 main tables:
1. **users** - User data
2. **user_devices** - Device data

Plus Laravel system tables (cache, jobs, sessions, migrations, personal_access_tokens, password_reset_tokens)

## Important Configuration Notes

### Environment Variables
Required `.env` variables:
```
APP_NAME=MoneyS
DB_DATABASE=moneys
API_KEY=your-secret-api-key-here
```

### API Key Authentication
- All API requests require `X-API-KEY` header
- Configured via `API_KEY` in `.env`
- Validated by `ValidateApiSecurityKey` middleware

## Common Patterns & Conventions

### API Response Format
All API responses follow this format:
```json
{
  "success": true,
  "message": "Success message",
  "data": {...}
}
```

Error response:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

### Validation
- Input validation via Form Requests
- Email uniqueness enforced (except for guest accounts where email is nullable)

### Password Reset
- Uses Laravel's built-in password reset functionality
- Requires email configuration in `.env`

## Swagger Documentation

- Annotations in controllers using `@OA\` tags
- Schemas defined in `app/Http/Controllers/Api/Schemas.php`
- Base controller with Swagger info: `app/Http/Controllers/Api/Controller.php`
- Generate docs: `php artisan l5-swagger:generate`
- View at: http://localhost:8000/api/documentation

## Testing Notes

- PHPUnit configured for Laravel 12
- Test structure follows Laravel conventions: `tests/Feature/` and `tests/Unit/`
