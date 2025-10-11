# 📖 MoneyS API Documentation

**Base URL**: `http://localhost:8000/api/v1`

**Authentication**: Bearer Token (Laravel Sanctum)

**API Security**: All requests require `X-API-Key` header

**Subscription Tiers**:
- **FREE**: 3 subscriptions max, 1 day notification
- **PRO**: Unlimited subscriptions, custom notifications, reports, export ($10/year)

📋 **[Tier System API Documentation](TIER_SYSTEM_API.md)** - Chi tiết về FREE vs PRO

---

## 🔑 API Security Key

**IMPORTANT**: All API requests must include the `X-API-Key` header for security.

```bash
X-API-Key: YOUR_API_SECURITY_KEY
```

### Where to get the API Key:
- The API key is configured in the backend `.env` file as `API_SECURITY_KEY`
- Contact your backend administrator to get the key
- Store the key securely in your mobile app (use secure storage, not hardcoded)

### Example Request:
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_SECURITY_KEY" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "full_name": "John Doe"
  }'
```

### Error Response (Missing or Invalid Key):
**401 Unauthorized**
```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing API security key."
}
```

### Security Best Practices:
- ✅ Store API key in secure storage (iOS Keychain, Android KeyStore)
- ✅ Use ProGuard/R8 obfuscation for Android apps
- ✅ Implement certificate pinning for HTTPS connections
- ❌ Never hardcode the API key in source code
- ❌ Never expose the key in public repositories
- ❌ Never log the API key in production

---

## 🔐 Authentication

### 1. Register User
**POST** `/register`

Tạo tài khoản user mới. Nếu có `device_id` trùng với guest account, tự động upgrade guest account thành tài khoản thật.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "full_name": "John Doe",
  "device_id": "unique-device-id-12345"
}
```

**Fields:**
- `email` (required): Email address
- `password` (required): Password (min 8 characters)
- `full_name` (required): User's full name
- `device_id` (optional): Device identifier for guest account merging
- `is_guest` (optional): Set to `true` to create guest account without password

**Response** (201 Created - New Account):
```json
{
  "user": {
    "id": "uuid-here",
    "email": "user@example.com",
    "full_name": "John Doe",
    "is_guest": false,
    "device_id": "unique-device-id-12345",
    "last_logged_in": "2025-10-11T10:00:00.000000Z",
    "subscription_tier": "free"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxx"
}
```

**Response** (200 OK - Guest Account Upgraded):
```json
{
  "user": {
    "id": "existing-guest-uuid",
    "email": "user@example.com",
    "full_name": "John Doe",
    "is_guest": false,
    "device_id": "unique-device-id-12345",
    "last_logged_in": "2025-10-11T10:00:00.000000Z"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxx",
  "message": "Guest account upgraded successfully"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_SECURITY_KEY" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "full_name": "John Doe",
    "device_id": "unique-device-id-12345"
  }'
```

**Guest Account Registration:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_SECURITY_KEY" \
  -d '{
    "email": "guest@example.com",
    "full_name": "Guest User",
    "is_guest": true,
    "device_id": "unique-device-id-12345"
  }'
```

---

### 2. Login
**POST** `/login`

Đăng nhập và nhận token. Tự động cập nhật `last_logged_in` và `device_id`.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_id": "unique-device-id-12345"
}
```

**Fields:**
- `email` (required): Email address
- `password` (required): Password
- `device_id` (optional): Device identifier to track user's device

**Response** (200 OK):
```json
{
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "full_name": "John Doe",
    "device_id": "unique-device-id-12345",
    "last_logged_in": "2025-10-11T10:30:00.000000Z",
    "subscription_tier": "free"
  },
  "token": "2|xxxxxxxxxxxxxxxxxxxx"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_SECURITY_KEY" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "device_id": "unique-device-id-12345"
  }'
```

---

### 3. Create Guest Account
**POST** `/guest`

Tạo tài khoản khách tạm thời. Nếu `device_id` đã tồn tại cho guest account khác, trả về account đó thay vì tạo mới.

**Request Body:**
```json
{
  "device_id": "unique-device-id-12345"
}
```

**Fields:**
- `device_id` (required): Device identifier to retrieve or create guest account

**Response** (201 Created - New Guest):
```json
{
  "user": {
    "id": "uuid-here",
    "email": "guest_uuid@moneys.app",
    "full_name": "Guest User",
    "is_guest": true,
    "device_id": "unique-device-id-12345",
    "last_logged_in": "2025-10-11T10:00:00.000000Z"
  },
  "token": "3|xxxxxxxxxxxxxxxxxxxx"
}
```

**Response** (200 OK - Existing Guest Retrieved):
```json
{
  "user": {
    "id": "existing-uuid",
    "email": "guest_existing@moneys.app",
    "full_name": "Guest User",
    "is_guest": true,
    "device_id": "unique-device-id-12345",
    "last_logged_in": "2025-10-11T10:15:00.000000Z"
  },
  "token": "4|xxxxxxxxxxxxxxxxxxxx",
  "message": "Existing guest account retrieved"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/guest \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_SECURITY_KEY" \
  -d '{
    "device_id": "unique-device-id-12345"
  }'
```

**Use Case:**
- User opens app for first time → Call `/guest` with device ID
- App stores token and user data
- If user reopens app → Call `/guest` again with same device ID → Gets same guest account back
- When user signs up → Call `/register` with device ID → Guest account upgraded to real account with all data preserved

---

### 4. Logout
**POST** `/logout` 🔒

Đăng xuất và xóa token hiện tại.

**Headers:**
- `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "message": "Logged out successfully"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 5. Refresh Token
**POST** `/refresh` 🔒

Làm mới token (xóa tokens cũ và tạo mới).

**Response** (200 OK):
```json
{
  "user": { ... },
  "token": "4|xxxxxxxxxxxxxxxxxxxx"
}
```

---

### 6. Upgrade Guest to Regular User
**POST** `/upgrade-guest` 🔒

Chuyển tài khoản guest thành tài khoản thường.

**Request Body:**
```json
{
  "email": "real@example.com",
  "password": "password123",
  "full_name": "Real Name"
}
```

**Response** (200 OK):
```json
{
  "user": { ... },
  "message": "Guest account upgraded successfully"
}
```

---

## 👤 User Management

### 7. Get Current User
**GET** `/users/me` 🔒

Lấy thông tin user đang đăng nhập.

**Response** (200 OK):
```json
{
  "id": "uuid",
  "email": "user@example.com",
  "full_name": "John Doe",
  "avatar_url": null,
  "is_guest": false,
  "device_id": "unique-device-id-12345",
  "last_logged_in": "2025-10-11T10:30:00.000000Z",
  "language": "en",
  "currency": "USD",
  "theme": "light",
  "notifications_enabled": true,
  "email_notifications": true,
  "subscription_tier": "free",
  "subscription_expires_at": null,
  "created_at": "2025-10-09T10:00:00.000000Z"
}
```

**cURL Example:**
```bash
curl http://localhost:8000/api/v1/users/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-API-Key: YOUR_API_SECURITY_KEY"
```

---

### 8. Update User Profile
**PATCH** `/users/me` 🔒

Cập nhật thông tin user.

**Request Body:**
```json
{
  "full_name": "New Name",
  "avatar_url": "https://example.com/avatar.jpg",
  "language": "vi",
  "currency": "VND",
  "theme": "dark",
  "notifications_enabled": false,
  "email_notifications": true
}
```

**Response** (200 OK):
```json
{
  "id": "uuid",
  "full_name": "New Name",
  ...
}
```

**cURL Example:**
```bash
curl -X PATCH http://localhost:8000/api/v1/users/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "New Name",
    "theme": "dark"
  }'
```

---

### 9. Delete Account
**DELETE** `/users/me` 🔒

Xóa tài khoản (soft delete).

**Response** (200 OK):
```json
{
  "message": "Account deleted successfully"
}
```

---

### 10. Update FCM Token
**POST** `/users/fcm-token` 🔒

Cập nhật Firebase Cloud Messaging token cho push notifications.

**Request Body:**
```json
{
  "fcm_token": "firebase-token-here"
}
```

**Response** (200 OK):
```json
{
  "message": "FCM token updated successfully"
}
```

---

### 11. Get Tier Information ⭐ NEW
**GET** `/users/tier-info` 🔒

Lấy thông tin tier hiện tại, limits và usage.

**Response** (200 OK):
```json
{
  "current_tier": "free",
  "limits": {
    "max_subscriptions": 3,
    "notification_days_before": [1],
    "can_customize_notifications": false,
    "can_export": false,
    "can_view_reports": false,
    "history_days": 30
  },
  "usage": {
    "subscriptions": {
      "current": 2,
      "max": 3,
      "percentage": 66.67
    }
  },
  "can_upgrade": true,
  "subscription_expires_at": null
}
```

**cURL Example:**
```bash
curl http://localhost:8000/api/v1/users/tier-info \
  -H "Authorization: Bearer YOUR_TOKEN"
```

📋 **Chi tiết**: Xem [TIER_SYSTEM_API.md](TIER_SYSTEM_API.md#1-get-tier-information)

---

### 12. Upgrade to PRO ⭐ NEW
**POST** `/users/upgrade-to-pro` 🔒

Nâng cấp lên PRO tier ($10/năm).

**Response** (200 OK):
```json
{
  "message": "Successfully upgraded to PRO",
  "user": {
    "subscription_tier": "pro",
    "subscription_expires_at": "2026-10-10T00:00:00.000000Z",
    ...
  }
}
```

**Response** (400 - Already PRO):
```json
{
  "error": "Already on PRO tier",
  "message": "You are already a PRO user."
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/users/upgrade-to-pro \
  -H "Authorization: Bearer YOUR_TOKEN"
```

📋 **Chi tiết**: Xem [TIER_SYSTEM_API.md](TIER_SYSTEM_API.md#2-upgrade-to-pro)

---

## 💳 Subscriptions

### 13. List Subscriptions
**GET** `/subscriptions` 🔒

Lấy danh sách subscriptions với filters và pagination.

**Query Parameters:**
- `category` (optional) - Filter by category
- `is_cancelled` (optional) - Filter cancelled (true/false)
- `per_page` (optional) - Items per page (default: 15)

**Response** (200 OK):
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "uuid",
      "name": "Netflix",
      "icon_url": "https://...",
      "price": "15.99",
      "currency": "USD",
      "start_date": "2025-01-01",
      "billing_cycle_count": 1,
      "billing_cycle_period": "month",
      "category": "Entertainment",
      "notes": "Premium plan",
      "is_cancelled": false,
      "next_billing_date": "2025-11-01"
    }
  ],
  "per_page": 15,
  "total": 5
}
```

**cURL Example:**
```bash
# All subscriptions
curl http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by category
curl "http://localhost:8000/api/v1/subscriptions?category=Entertainment" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Only cancelled subscriptions
curl "http://localhost:8000/api/v1/subscriptions?is_cancelled=true" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 14. Create Subscription ⭐ UPDATED
**POST** `/subscriptions` 🔒

Tạo subscription mới.

**Request Body:**
```json
{
  "name": "Netflix",
  "icon_url": "https://example.com/netflix.png",
  "price": 15.99,
  "currency": "USD",
  "start_date": "2025-01-01",
  "billing_cycle_count": 1,
  "billing_cycle_period": "month",
  "category": "Entertainment",
  "notes": "Premium plan"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `price`: required, numeric, min:0
- `currency_code`: required, string, size:3
- `start_date`: required, date
- `billing_cycle_count`: required, integer, min:1
- `billing_cycle_period`: required, in:day,month,quarter,year
- `category`: optional, string, max:255
- `notes`: optional, string

**Response** (201 Created):
```json
{
  "id": "uuid",
  "name": "Netflix",
  "price": "15.99",
  ...
}
```

**Response** (403 - FREE Tier Limit Reached):
```json
{
  "error": "Subscription limit reached",
  "message": "You have reached the maximum limit of 3 subscriptions for the Free plan. Upgrade to PRO for unlimited subscriptions.",
  "current_tier": "free",
  "current_count": 3,
  "limit": 3,
  "upgrade_required": true
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Netflix",
    "price": 15.99,
    "currency": "USD",
    "start_date": "2025-01-01",
    "billing_cycle_count": 1,
    "billing_cycle_period": "month",
    "category": "Entertainment"
  }'
```

**Note**: FREE users bị giới hạn 3 subscriptions. PRO users unlimited.

📋 **Chi tiết**: Xem [TIER_SYSTEM_API.md](TIER_SYSTEM_API.md#3-create-subscription-with-limit-check)

---

### 15. Get Subscription Statistics
**GET** `/subscriptions/stats` 🔒

Lấy thống kê chi tiêu subscription.

**Response** (200 OK):
```json
{
  "total_monthly_cost": 125.50,
  "paid_amount": 35.67,
  "remaining_amount": 89.83,
  "subscription_count": 8
}
```

**Calculation Logic:**
- `total_monthly_cost`: Tổng chi phí hàng tháng từ tất cả active subscriptions
- `paid_amount`: Số tiền đã trả trong tháng hiện tại (tính theo ngày)
- `remaining_amount`: Số tiền còn phải trả đến hết tháng
- `subscription_count`: Số lượng active subscriptions

**cURL Example:**
```bash
curl http://localhost:8000/api/v1/subscriptions/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 16. Get Single Subscription
**GET** `/subscriptions/{id}` 🔒

Lấy chi tiết 1 subscription.

**Response** (200 OK):
```json
{
  "id": "uuid",
  "name": "Netflix",
  "price": "15.99",
  "next_billing_date": "2025-11-01",
  ...
}
```

---

### 17. Update Subscription
**PATCH** `/subscriptions/{id}` 🔒

Cập nhật subscription.

**Request Body:**
```json
{
  "name": "Netflix Premium",
  "price": 19.99,
  "category": "Streaming"
}
```

**Response** (200 OK):
```json
{
  "id": "uuid",
  "name": "Netflix Premium",
  "price": "19.99",
  ...
}
```

---

### 18. Delete Subscription
**DELETE** `/subscriptions/{id}` 🔒

Xóa subscription (soft delete).

**Response** (200 OK):
```json
{
  "message": "Subscription deleted successfully"
}
```

---

### 19. Cancel Subscription
**POST** `/subscriptions/{id}/cancel` 🔒

Đánh dấu subscription là cancelled (không xóa).

**Response** (200 OK):
```json
{
  "message": "Subscription cancelled successfully",
  "subscription": {
    "id": "uuid",
    "is_cancelled": true,
    "cancelled_at": "2025-10-09T15:00:00.000000Z",
    ...
  }
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/subscriptions/uuid-here/cancel \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📅 Calendar

### 20. Get Calendar View
**GET** `/calendar/{year}/{month}` 🔒

Lấy subscriptions grouped by ngày billing trong tháng.

**Path Parameters:**
- `year`: Year (e.g., 2025)
- `month`: Month (1-12)

**Response** (200 OK):
```json
{
  "year": 2025,
  "month": 10,
  "calendar": [
    {
      "date": "2025-10-01",
      "subscriptions": [
        {
          "id": "uuid",
          "name": "Netflix",
          "icon_url": "...",
          "price": "15.99",
          "currency": "USD",
          "category": "Entertainment"
        }
      ]
    },
    {
      "date": "2025-10-15",
      "subscriptions": [...]
    }
  ]
}
```

**cURL Example:**
```bash
# Get October 2025 calendar
curl http://localhost:8000/api/v1/calendar/2025/10 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🔔 Notifications

### 21. List Notifications
**GET** `/notifications` 🔒

Lấy danh sách notifications.

**Query Parameters:**
- `is_read` (optional) - Filter by read status (true/false)
- `per_page` (optional) - Items per page (default: 15)

**Response** (200 OK):
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "uuid",
      "title": "Renewal Due Tomorrow",
      "message": "Netflix renewal of USD 15.99 is due tomorrow.",
      "notification_type": "renewal",
      "is_read": false,
      "read_at": null,
      "created_at": "2025-10-09T09:00:00.000000Z"
    }
  ],
  "total": 3
}
```

**cURL Example:**
```bash
# All notifications
curl http://localhost:8000/api/v1/notifications \
  -H "Authorization: Bearer YOUR_TOKEN"

# Only unread
curl "http://localhost:8000/api/v1/notifications?is_read=false" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 22. Mark Notification as Read
**PATCH** `/notifications/{id}/read` 🔒

Đánh dấu notification đã đọc.

**Response** (200 OK):
```json
{
  "message": "Notification marked as read",
  "notification": {
    "id": "uuid",
    "is_read": true,
    "read_at": "2025-10-09T15:30:00.000000Z",
    ...
  }
}
```

**cURL Example:**
```bash
curl -X PATCH http://localhost:8000/api/v1/notifications/uuid-here/read \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📋 Common Patterns

### Required Headers

**All endpoints require:**
```
X-API-Key: YOUR_API_SECURITY_KEY
```

**Protected endpoints (🔒) also require:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Example:**
```bash
curl http://localhost:8000/api/v1/users/me \
  -H "X-API-Key: YOUR_API_SECURITY_KEY" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Error Responses

**401 Unauthorized (Missing/Invalid API Key):**
```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing API security key."
}
```

**401 Unauthorized (Missing/Invalid Token):**
```json
{
  "message": "Unauthenticated."
}
```

**422 Validation Error:**
```json
{
  "message": "The email field is required.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

**404 Not Found:**
```json
{
  "message": "Not found."
}
```

**500 Server Error:**
```json
{
  "message": "Server Error"
}
```

---

## 🧪 Testing với cURL

### Full Workflow Example:

```bash
# Set API key (get from backend admin)
API_KEY="YOUR_API_SECURITY_KEY"

# 1. Create guest account
GUEST_TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/guest \
  -H "Content-Type: application/json" \
  -H "X-API-Key: $API_KEY" \
  -d '{"device_id":"my-device-123"}' \
  | jq -r '.token')

# 2. Register real account (upgrades guest)
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "X-API-Key: $API_KEY" \
  -d '{
    "email":"test@test.com",
    "password":"password123",
    "full_name":"Test User",
    "device_id":"my-device-123"
  }' \
  | jq -r '.token')

# 3. Create subscription
curl -X POST http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Netflix",
    "price": 15.99,
    "currency": "USD",
    "start_date": "2025-01-01",
    "billing_cycle_count": 1,
    "billing_cycle_period": "month",
    "category": "Entertainment"
  }'

# 4. Get stats
curl http://localhost:8000/api/v1/subscriptions/stats \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-API-Key: $API_KEY"

# 5. List subscriptions
curl http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-API-Key: $API_KEY"
```

---

## 🎯 Postman Collection

Import vào Postman:
1. New Collection → Import
2. Copy/paste file: [MoneyS.postman_collection.json](MoneyS.postman_collection.json)

---

## 🔐 Rate Limiting

- Default: 60 requests/minute per IP
- Authenticated: 1000 requests/minute per user

---

## 📱 SDK Support

Coming soon:
- JavaScript/TypeScript SDK
- Flutter/Dart SDK
- Swift SDK (iOS)
- Kotlin SDK (Android)

---

## 🆘 Support

- **Documentation**: [README.md](README.md)
- **Troubleshooting**: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Quick Fixes**: [QUICK_FIX.md](QUICK_FIX.md)

---

**Last Updated**: October 2025
**API Version**: v1
**Base URL**: http://localhost:8000/api/v1
