# 📖 MoneyS API Documentation

**Base URL**: `http://localhost:8000/api/v1`

**Authentication**: Bearer Token (Laravel Sanctum)

---

## 🔐 Authentication

### 1. Register User
**POST** `/register`

Tạo tài khoản user mới.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "full_name": "John Doe"
}
```

**Response** (201 Created):
```json
{
  "user": {
    "id": "uuid-here",
    "email": "user@example.com",
    "full_name": "John Doe",
    "is_guest": false,
    "subscription_tier": "free"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxx"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "full_name": "John Doe"
  }'
```

---

### 2. Login
**POST** `/login`

Đăng nhập và nhận token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response** (200 OK):
```json
{
  "user": { ... },
  "token": "2|xxxxxxxxxxxxxxxxxxxx"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

---

### 3. Create Guest Account
**POST** `/guest`

Tạo tài khoản khách tạm thời.

**Request Body:** (none)

**Response** (201 Created):
```json
{
  "user": {
    "id": "uuid-here",
    "email": "guest_uuid@moneys.app",
    "full_name": "Guest User",
    "is_guest": true
  },
  "token": "3|xxxxxxxxxxxxxxxxxxxx"
}
```

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/guest
```

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
  "locale": "en",
  "currency_code": "USD",
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
  -H "Authorization: Bearer YOUR_TOKEN"
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
  "locale": "vi",
  "currency_code": "VND",
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

## 💳 Subscriptions

### 11. List Subscriptions
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
      "currency_code": "USD",
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

### 12. Create Subscription
**POST** `/subscriptions` 🔒

Tạo subscription mới.

**Request Body:**
```json
{
  "name": "Netflix",
  "icon_url": "https://example.com/netflix.png",
  "price": 15.99,
  "currency_code": "USD",
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

**cURL Example:**
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

---

### 13. Get Subscription Statistics
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

### 14. Get Single Subscription
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

### 15. Update Subscription
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

### 16. Delete Subscription
**DELETE** `/subscriptions/{id}` 🔒

Xóa subscription (soft delete).

**Response** (200 OK):
```json
{
  "message": "Subscription deleted successfully"
}
```

---

### 17. Cancel Subscription
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

### 18. Get Calendar View
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
          "currency_code": "USD",
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

### 19. List Notifications
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

### 20. Mark Notification as Read
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

### Authorization Header
Tất cả endpoints có 🔒 cần header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Error Responses

**401 Unauthorized:**
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
# 1. Register
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password123","full_name":"Test User"}' \
  | jq -r '.token')

# 2. Create subscription
curl -X POST http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer $TOKEN" \
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

# 3. Get stats
curl http://localhost:8000/api/v1/subscriptions/stats \
  -H "Authorization: Bearer $TOKEN"

# 4. List subscriptions
curl http://localhost:8000/api/v1/subscriptions \
  -H "Authorization: Bearer $TOKEN"
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
