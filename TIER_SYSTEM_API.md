# MoneyS - Tier System API Documentation

## Overview

MoneyS có 2 gói subscription:
- **FREE**: Tối đa 3 subscriptions, nhắc nhở 1 ngày trước
- **PRO**: Không giới hạn subscriptions, custom nhắc nhở, reports, export ($10/năm)

---

## Tier Configuration

### FREE Tier
- **Max Subscriptions**: 3
- **Notification Days**: 1 day before payment
- **History**: 30 days
- **Reports**: ❌ No
- **Export**: ❌ No
- **Custom Notifications**: ❌ No

### PRO Tier
- **Max Subscriptions**: ∞ Unlimited
- **Notification Days**: Customizable (user can set: 1, 3, 7 days or any)
- **History**: ∞ Unlimited
- **Reports**: ✅ Yes (advanced analytics, charts)
- **Export**: ✅ Yes (PDF, CSV)
- **Custom Notifications**: ✅ Yes
- **Price**: $10.00/year

---

## New API Endpoints

### 1. Get Tier Information

**GET** `/api/v1/users/tier-info`

Get current user's tier information, limits, and usage.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
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

**Response for PRO user:**
```json
{
  "current_tier": "pro",
  "limits": {
    "max_subscriptions": null,
    "notification_days_before": null,
    "can_customize_notifications": true,
    "can_export": true,
    "can_view_reports": true,
    "history_days": null,
    "price_yearly": 10.00,
    "currency": "USD"
  },
  "usage": {
    "subscriptions": {
      "current": 15,
      "max": "unlimited",
      "percentage": 0
    }
  },
  "can_upgrade": false,
  "subscription_expires_at": "2026-10-10T00:00:00.000000Z"
}
```

**cURL Example:**
```bash
curl -X GET "http://localhost:8000/api/v1/users/tier-info" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

### 2. Upgrade to PRO

**POST** `/api/v1/users/upgrade-to-pro`

Upgrade user from FREE to PRO tier.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "message": "Successfully upgraded to PRO",
  "user": {
    "id": "9d5e8c1a-...",
    "email": "user@example.com",
    "full_name": "John Doe",
    "subscription_tier": "pro",
    "subscription_expires_at": "2026-10-10T00:00:00.000000Z",
    ...
  }
}
```

**Response 400 (Already PRO):**
```json
{
  "error": "Already on PRO tier",
  "message": "You are already a PRO user."
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8000/api/v1/users/upgrade-to-pro" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Note:** Hiện tại endpoint này upgrade trực tiếp (for testing). Trong production cần integrate với payment gateway (Stripe, PayPal, etc.)

---

## Modified Endpoints

### 3. Create Subscription (with limit check)

**POST** `/api/v1/subscriptions`

Create new subscription with automatic limit checking.

**Middleware:** `CheckSubscriptionLimit` - tự động kiểm tra giới hạn theo tier

**Request Body:**
```json
{
  "name": "Netflix Premium",
  "price": 15.99,
  "currency_code": "USD",
  "start_date": "2025-10-10",
  "billing_cycle_count": 1,
  "billing_cycle_period": "month"
}
```

**Response 201 (Success):**
```json
{
  "id": "9d5e8c1a-...",
  "user_id": "9d5e8c1a-...",
  "name": "Netflix Premium",
  "price": 15.99,
  ...
}
```

**Response 403 (Limit Reached - FREE tier only):**
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

**Note:** PRO users không bao giờ gặp error này vì unlimited subscriptions.

---

## Feature Access Control

Các endpoint yêu cầu PRO tier sẽ có middleware `CheckFeatureAccess`.

### Example: Export Data (PRO only)

**GET** `/api/v1/subscriptions/export` (future endpoint)

**Middleware:** `CheckFeatureAccess:export_data`

**Response 403 (FREE user):**
```json
{
  "error": "Feature not available",
  "message": "This feature is only available for PRO users. Please upgrade your plan.",
  "feature": "export_data",
  "current_tier": "free",
  "upgrade_required": true
}
```

### Example: Advanced Reports (PRO only)

**GET** `/api/v1/reports/analytics` (future endpoint)

**Middleware:** `CheckFeatureAccess:advanced_reports`

**Response 403 (FREE user):**
```json
{
  "error": "Feature not available",
  "message": "This feature is only available for PRO users. Please upgrade your plan.",
  "feature": "advanced_reports",
  "current_tier": "free",
  "upgrade_required": true
}
```

---

## Implementation Guide for Mobile App

### 1. Check User Tier on App Start

```dart
// Fetch tier info when app starts
final response = await http.get(
  Uri.parse('$baseUrl/users/tier-info'),
  headers: {'Authorization': 'Bearer $token'},
);

final tierInfo = jsonDecode(response.body);
final currentTier = tierInfo['current_tier']; // "free" or "pro"
final limits = tierInfo['limits'];
final usage = tierInfo['usage'];
```

### 2. Show Upgrade Prompt

**Khi nào hiển thị upgrade prompt:**

1. **Khi tạo subscription thứ 3 (FREE user):**
   ```dart
   if (currentTier == 'free' && usage['subscriptions']['current'] == 2) {
     showDialog('Bạn sắp đạt giới hạn! Chỉ còn 1 subscription. Upgrade để không giới hạn.');
   }
   ```

2. **Khi API trả về 403 với `upgrade_required: true`:**
   ```dart
   if (response.statusCode == 403) {
     final error = jsonDecode(response.body);
     if (error['upgrade_required'] == true) {
       showUpgradeDialog(error['message']);
     }
   }
   ```

3. **Khi user cố truy cập tính năng PRO:**
   ```dart
   void onExportButtonPressed() {
     if (currentTier == 'free') {
       showProFeatureDialog('Export data chỉ dành cho PRO users. Upgrade ngay!');
     } else {
       // Call export API
     }
   }
   ```

### 3. Upgrade Flow

```dart
Future<void> upgradeToPro() async {
  // 1. Show payment screen (Stripe, IAP, etc.)
  final paymentSuccess = await processPayment();

  if (paymentSuccess) {
    // 2. Call upgrade API
    final response = await http.post(
      Uri.parse('$baseUrl/users/upgrade-to-pro'),
      headers: {'Authorization': 'Bearer $token'},
    );

    if (response.statusCode == 200) {
      // 3. Update local state
      setState(() {
        currentTier = 'pro';
      });

      // 4. Show success message
      showSuccessDialog('Bạn đã upgrade lên PRO thành công!');
    }
  }
}
```

### 4. UI/UX Recommendations

**FREE tier badge:**
```dart
if (currentTier == 'free') {
  Container(
    child: Text('FREE ${usage['subscriptions']['current']}/3'),
    // Show percentage bar
  );
}
```

**PRO features locked UI:**
```dart
GestureDetector(
  onTap: () {
    if (currentTier == 'free') {
      showUpgradeDialog();
    } else {
      // Open feature
    }
  },
  child: Stack(
    children: [
      FeatureButton(),
      if (currentTier == 'free')
        Positioned(
          top: 0,
          right: 0,
          child: Badge('PRO'),
        ),
    ],
  ),
);
```

---

## Admin Panel Features

### 1. PRO Features Management

Navigate to **Settings → PRO Features** để quản lý các tính năng PRO.

**Tính năng có sẵn:**
- Unlimited Subscriptions
- Custom Notification Days
- Advanced Reports
- Export Data
- Unlimited History

**Có thể:**
- Enable/Disable feature
- Chỉnh giá (nếu feature riêng lẻ)
- Thay đổi mô tả
- Sắp xếp thứ tự hiển thị

### 2. Subscription Settings

Navigate to **Settings → Subscription Settings** để cấu hình:

**FREE Tier:**
- Max subscriptions (default: 3)
- Notification days (default: 1)
- History days (default: 30)

**PRO Tier:**
- Yearly price (default: $10.00)
- Currency (default: USD)

**Note:** Hiện tại save settings chỉ hiển thị notification. Để lưu vào database/config, cần implement thêm logic lưu vào settings table hoặc update .env file.

---

## Testing Guide

### Test Case 1: FREE User Reaches Limit

```bash
# 1. Register as FREE user
curl -X POST "http://localhost:8000/api/v1/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "freeuser@test.com",
    "password": "password123",
    "full_name": "Free User"
  }'

# Save the token from response

# 2. Create 3 subscriptions
for i in {1..3}; do
  curl -X POST "http://localhost:8000/api/v1/subscriptions" \
    -H "Authorization: Bearer YOUR_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
      "name": "Subscription '$i'",
      "price": 9.99,
      "currency_code": "USD",
      "start_date": "2025-10-10",
      "billing_cycle_count": 1,
      "billing_cycle_period": "month"
    }'
done

# 3. Try to create 4th subscription (should fail)
curl -X POST "http://localhost:8000/api/v1/subscriptions" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Subscription 4",
    "price": 9.99,
    "currency_code": "USD",
    "start_date": "2025-10-10",
    "billing_cycle_count": 1,
    "billing_cycle_period": "month"
  }'

# Expected: 403 error with upgrade_required: true
```

### Test Case 2: Upgrade to PRO

```bash
# 1. Check tier info (should be FREE)
curl -X GET "http://localhost:8000/api/v1/users/tier-info" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 2. Upgrade to PRO
curl -X POST "http://localhost:8000/api/v1/users/upgrade-to-pro" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 3. Check tier info again (should be PRO)
curl -X GET "http://localhost:8000/api/v1/users/tier-info" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Create more subscriptions (should work now)
curl -X POST "http://localhost:8000/api/v1/subscriptions" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Subscription 4",
    "price": 9.99,
    "currency_code": "USD",
    "start_date": "2025-10-10",
    "billing_cycle_count": 1,
    "billing_cycle_period": "month"
  }'
```

---

## Database Schema

### ProFeatures Table

```sql
CREATE TABLE pro_features (
  id UUID PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  key VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  is_enabled BOOLEAN DEFAULT TRUE,
  price DECIMAL(10,2) DEFAULT 0,
  sort_order INTEGER DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

**Default Features:**
1. `unlimited_subscriptions` - Add unlimited subscriptions
2. `custom_notifications` - Customize reminder days
3. `advanced_reports` - View detailed reports and charts
4. `export_data` - Export to PDF and CSV
5. `unlimited_history` - Keep history forever

---

## Next Steps

### For Backend:
1. ✅ Tier limits config created
2. ✅ ProFeature model and migration
3. ✅ Middleware for limit checking
4. ✅ API endpoints for tier info and upgrade
5. ✅ Admin panel for feature management
6. ⏳ Integrate payment gateway (Stripe/PayPal)
7. ⏳ Implement export endpoints
8. ⏳ Implement reports endpoints
9. ⏳ Custom notification days per user

### For Mobile App:
1. Fetch tier info on app start
2. Show usage percentage (2/3 subscriptions)
3. Lock PRO features with badge
4. Show upgrade dialog when limit reached
5. Implement payment flow
6. Call upgrade API after payment
7. Update UI based on tier

---

## Support

Có thể liên hệ hoặc mở issue nếu cần:
- Thêm tính năng PRO mới
- Thay đổi giá PRO tier
- Implement payment gateway
- Custom logic cho từng feature
