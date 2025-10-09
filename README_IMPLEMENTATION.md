# MoneyS Backend - Laravel Implementation

Complete RESTful API backend for MoneyS subscription tracking application built with Laravel 12, Filament Admin, and Firebase notifications.

## Features Implemented

### 1. Database Schema
- **Users**: Extended with UUID primary key, soft deletes, guest accounts, FCM tokens, localization, themes, notification preferences, subscription tiers
- **Subscriptions**: Subscription management with flexible billing cycles, categories, cancellation tracking
- **Notifications**: In-app and push notifications for renewal reminders
- **Payment Plans**: Subscription tier management with features and limits

### 2. API Endpoints (Prefix: `/api/v1`)

#### Authentication (Public)
- `POST /register` - Register new user
- `POST /login` - Login with email/password
- `POST /guest` - Create temporary guest account

#### Authentication (Protected)
- `POST /logout` - Logout current session
- `POST /refresh` - Refresh authentication token
- `POST /upgrade-guest` - Convert guest to regular account

#### User Management
- `GET /users/me` - Get current user profile
- `PATCH /users/me` - Update user profile
- `DELETE /users/me` - Delete user account (soft delete)
- `POST /users/fcm-token` - Update Firebase Cloud Messaging token

#### Subscriptions
- `GET /subscriptions` - List subscriptions (with filters: category, is_cancelled)
- `POST /subscriptions` - Create new subscription
- `GET /subscriptions/stats` - Get subscription statistics (monthly cost, paid/remaining amounts)
- `GET /subscriptions/{id}` - Get single subscription
- `PATCH /subscriptions/{id}` - Update subscription
- `DELETE /subscriptions/{id}` - Delete subscription (soft delete)
- `POST /subscriptions/{id}/cancel` - Cancel subscription

#### Calendar
- `GET /calendar/{year}/{month}` - Get subscriptions grouped by billing date for a month

#### Notifications
- `GET /notifications` - List notifications (filter by is_read)
- `PATCH /notifications/{id}/read` - Mark notification as read

### 3. Models

#### User
- Traits: `HasApiTokens`, `HasUuids`, `SoftDeletes`, `Notifiable`
- Relations: `subscriptions()`, `activeSubscriptions()`, `notifications()`
- Authentication via Laravel Sanctum

#### Subscription
- Traits: `HasUuids`, `SoftDeletes`
- Relation: `user()`
- Accessor: `next_billing_date` (calculates next renewal date)
- Scope: `dueInDays($days)` - find subscriptions due in X days

#### Notification
- Traits: `HasUuids`
- Relations: `user()`, `subscription()`
- Method: `markAsRead()`

#### PaymentPlan
- Traits: `HasUuids`
- Features stored as JSON

### 4. Services

#### FirebaseService
- Initializes Firebase SDK with service account credentials
- `sendNotification($token, $title, $body, $data)` - Send push notifications via FCM

### 5. Jobs (Queued)

#### CheckUpcomingRenewals
- Finds subscriptions due in X days
- Dispatches `SendRenewalNotification` for each

#### SendRenewalNotification
- Checks user notification preferences
- Sends Firebase push notification if FCM token exists
- Creates database notification record
- Dynamic messages based on days ahead (today/tomorrow/X days)

### 6. Scheduled Tasks

Configured in `routes/console.php`:
- **08:00 daily**: Check subscriptions due today (0 days)
- **09:30 daily**: Check subscriptions due tomorrow (1 day)
- **09:00 daily**: Check subscriptions due in 3 days

### 7. Filament Admin Panel

#### UserResource
- **Table Columns**: full_name, email, is_guest, subscription_tier (badge), subscriptions_count, created_at
- **Filters**: subscription_tier, is_guest
- **Form**: email, full_name, is_guest toggle, subscription_tier select, theme select, notifications_enabled toggle

#### SubscriptionResource
- **Table Columns**: user.full_name, name, price (with currency), category (badge), is_cancelled, start_date
- **Filters**: category (dynamic from DB), is_cancelled
- **Form**: user_id select (searchable), name, icon_url, price, currency_code, start_date, billing_cycle_count, billing_cycle_period, category, notes, is_cancelled

### 8. Validation Rules

All API endpoints include comprehensive validation:
- Email: `required|email|unique` for registration
- Password: `min:8` characters
- Price: `numeric|min:0`
- Currency: `string|size:3` (ISO 4217 codes)
- Billing cycle count: `integer|min:1`

## Installation & Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Configuration

Update `.env`:
```env
APP_NAME=MoneyS
DB_DATABASE=moneys
QUEUE_CONNECTION=redis

# Firebase credentials path
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
```

### 3. Database Setup
```bash
php artisan migrate
```

### 4. Create Filament Admin User
```bash
php artisan make:filament-user
```

### 5. Firebase Setup

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or use existing
3. Navigate to Project Settings → Service Accounts
4. Click "Generate New Private Key"
5. Save the JSON file and update `FIREBASE_CREDENTIALS` in `.env`

### 6. Start Queue Worker
```bash
php artisan queue:work
```

Or use Laravel Horizon:
```bash
php artisan horizon
```

### 7. Start Scheduler (for cron jobs)
```bash
php artisan schedule:work
```

In production, add to crontab:
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Serve Application
```bash
php artisan serve
```

Access:
- **API**: http://localhost:8000/api/v1
- **Admin Panel**: http://localhost:8000/admin

## API Usage Examples

### Register User
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "full_name": "John Doe"
  }'
```

### Create Guest Account
```bash
curl -X POST http://localhost:8000/api/v1/guest
```

### Create Subscription (Authenticated)
```bash
curl -X POST http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Netflix",
    "price": 15.99,
    "currency_code": "USD",
    "start_date": "2025-01-01",
    "billing_cycle_count": 1,
    "billing_cycle_period": "month",
    "category": "Entertainment"
  }'
```

### Get Subscription Stats
```bash
curl http://localhost:8000/api/v1/subscriptions/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get Calendar View
```bash
curl http://localhost:8000/api/v1/calendar/2025/10 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Tech Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum (token-based API auth)
- **Admin Panel**: Filament v3
- **Queue System**: Laravel Horizon + Redis
- **Push Notifications**: Firebase Cloud Messaging (kreait/firebase-php)
- **Database**: MySQL (with UUIDs and soft deletes)
- **PHP**: 8.2+

## Architecture Highlights

### UUID Primary Keys
All main models use UUID instead of auto-incrementing integers for better security and distributed systems compatibility.

### Soft Deletes
Users and subscriptions use soft deletes to maintain data integrity and allow recovery.

### Queue System
Background jobs for sending notifications use Redis queue for better performance and scalability.

### Scheduled Tasks
Automated renewal reminders run daily at scheduled times using Laravel's task scheduler.

### Next Billing Date Calculation
Smart algorithm calculates next billing date based on:
- Start date
- Billing cycle count
- Billing cycle period (day/month/quarter/year)

### Modular Structure
- Controllers handle HTTP requests and validation
- Models contain business logic and relationships
- Services handle external integrations (Firebase)
- Jobs handle background tasks
- Resources provide admin interface

## Security Features

- Password hashing with bcrypt
- API authentication via Sanctum tokens
- Email validation and uniqueness checks
- Input validation on all endpoints
- CSRF protection on web routes
- Guest account isolation

## Future Enhancements

Potential additions:
- Email notifications for renewals
- Payment integration (Stripe/PayPal)
- Multi-currency support with exchange rates
- Subscription analytics and reports
- Recurring payment tracking
- Budget alerts and limits
- Subscription sharing (family plans)
- Export data to CSV/PDF

## Support

For issues or questions:
- Check Laravel documentation: https://laravel.com/docs
- Check Filament documentation: https://filamentphp.com/docs
- Review API endpoint documentation above

---

**Implementation Date**: October 2025
**Laravel Version**: 12.x
**PHP Version**: 8.2+
