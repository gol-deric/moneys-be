# MoneyS Backend - Changelog

## [Unreleased] - 2025-10-10

### Added
- **Admin System**: Added `is_admin` field to users table
  - Only admin users can access Filament admin panel at `/admin`
  - Admin API endpoints at `/api/v1/admin/*` for managing users
  - Admin middleware `IsAdmin` to protect admin-only routes

- **Subscription Tiers**: Simplified to 2 tiers only
  - **FREE**: Max 3 subscriptions, 1 day notification
  - **PRO**: Unlimited subscriptions, custom notifications, reports, export ($10/year)
  - Migration to update existing 'enterprise' and 'premium' tiers to 'pro'

- **User Fields**: Updated user fields for better clarity
  - Renamed `locale` → `language` (e.g., "en", "vi")
  - Renamed `currency_code` → `currency` (e.g., "USD", "VND")

- **Tier System Features**:
  - ProFeature model to manage PRO features dynamically
  - Middleware `CheckSubscriptionLimit` - prevents FREE users from creating >3 subscriptions
  - Middleware `CheckFeatureAccess` - checks PRO feature access
  - API endpoints: `/users/tier-info`, `/users/upgrade-to-pro`
  - Filament resources: ProFeature management, Subscription Settings page

### Changed
- **Database**:
  - `personal_access_tokens` table now supports UUID for `tokenable_id`
  - `users.subscription_tier` enum changed from ['free', 'premium', 'enterprise'] to ['free', 'pro']
  - Added `users.is_admin` boolean field
  - Renamed `users.locale` to `users.language`
  - Renamed `users.currency_code` to `users.currency`

- **API**:
  - Updated all user-related endpoints to use `language` and `currency` instead of `locale` and `currency_code`
  - Added validation for subscription_tier to only accept 'free' or 'pro'

- **Admin Panel (Filament)**:
  - User resource now shows `is_admin`, `language`, and `currency` columns
  - Subscription tier filter updated to show only FREE and PRO
  - Added Admin Users filter
  - Settings menu with PRO Features and Subscription Settings pages

### Fixed
- Fixed `personal_access_tokens` UUID compatibility issue
- Fixed subscription tier enum to prevent data truncation errors
- Fixed Filament Settings page form actions error

### Admin API Endpoints (New)
All require admin authentication:
- `GET /api/v1/admin/users` - List all users
- `POST /api/v1/admin/users` - Create new user
- `GET /api/v1/admin/users/{id}` - Get user details
- `PATCH /api/v1/admin/users/{id}` - Update user
- `DELETE /api/v1/admin/users/{id}` - Delete user
- `GET /api/v1/admin/users/{id}/subscriptions` - Get user's subscriptions

### Documentation
- Created `TIER_SYSTEM_API.md` - Complete tier system documentation
- Updated `API_DOCUMENTATION.md` with tier endpoints
- Updated `SUBSCRIPTION_TIERS.md` with FREE vs PRO comparison
- Created `CHANGELOG.md` to track changes

### Default Admin User
- Email: `admin@moneys.com`
- Password: `password`
- Admin Access: YES
- Tier: PRO

---

## API Breaking Changes

⚠️ **Important**: The following fields have been renamed in API responses and requests:

### Before:
```json
{
  "locale": "en",
  "currency_code": "USD",
  "subscription_tier": "enterprise"
}
```

### After:
```json
{
  "language": "en",
  "currency": "USD",
  "subscription_tier": "pro"
}
```

**Migration Guide for Mobile Apps:**
1. Update all API calls from `locale` to `language`
2. Update all API calls from `currency_code` to `currency`
3. Update tier checking logic to use 'free' or 'pro' (not 'premium' or 'enterprise')
4. Handle new tier limit responses (403 errors when FREE users exceed 3 subscriptions)

---

## Configuration

### Tier Limits (`config/tier_limits.php`)
```php
'free' => [
    'max_subscriptions' => 3,
    'notification_days_before' => [1],
    'can_customize_notifications' => false,
    'can_export' => false,
    'can_view_reports' => false,
    'history_days' => 30,
],

'pro' => [
    'max_subscriptions' => null, // unlimited
    'notification_days_before' => null, // customizable
    'can_customize_notifications' => true,
    'can_export' => true,
    'can_view_reports' => true,
    'history_days' => null, // unlimited
    'price_yearly' => 10.00,
    'currency' => 'USD',
],
```

---

## Database Schema Changes

### Users Table
| Field | Type | Description | Changed |
|-------|------|-------------|---------|
| `language` | varchar(10) | User's language (en, vi, etc.) | ✅ Renamed from `locale` |
| `currency` | varchar(3) | User's currency (USD, VND, etc.) | ✅ Renamed from `currency_code` |
| `is_admin` | boolean | Admin access flag | ✅ New field |
| `subscription_tier` | enum('free','pro') | Subscription tier | ✅ Updated enum |

### New Tables
- `pro_features` - Manages PRO tier features

---

## Next Steps (TODO)

- [ ] Integrate payment gateway (Stripe/PayPal) for PRO upgrade
- [ ] Implement export endpoints (PDF/CSV)
- [ ] Implement advanced reports endpoints
- [ ] Add custom notification days per user for PRO tier
- [ ] Clean up old history for FREE users (>30 days)
- [ ] Add email notifications for tier limits
- [ ] Implement family/group sharing for PRO users
