# Google Play Billing Integration - MoneyS

## Overview

MoneyS hỗ trợ nâng cấp từ FREE lên PRO thông qua Google Play Billing API. Backend sẽ verify purchase và tự động nâng cấp user lên tier PRO.

---

## Table of Contents

1. [Setup Backend](#setup-backend)
2. [Setup Google Play Console](#setup-google-play-console)
3. [API Endpoints](#api-endpoints)
4. [Mobile App Integration](#mobile-app-integration)
5. [Testing](#testing)
6. [Production Deployment](#production-deployment)

---

## Setup Backend

### 1. Environment Variables

Thêm vào `.env`:

```env
# Google Play Billing
GOOGLE_PLAY_CREDENTIALS_PATH=/path/to/google-play-service-account.json
GOOGLE_PLAY_PACKAGE_NAME=com.moneys.app
```

### 2. Database Schema

Đã tạo sẵn bảng `purchases`:

```sql
CREATE TABLE purchases (
  id UUID PRIMARY KEY,
  user_id UUID (FK to users),
  product_id VARCHAR (e.g., "moneys_pro_yearly"),
  order_id VARCHAR UNIQUE (Google Play order ID),
  purchase_token VARCHAR UNIQUE (Google Play purchase token),
  receipt_data TEXT (full receipt JSON),
  platform ENUM('google_play', 'app_store', 'web'),
  purchase_type ENUM('subscription', 'one_time'),
  amount DECIMAL(10,2),
  currency VARCHAR(3),
  purchased_at TIMESTAMP,
  expires_at TIMESTAMP,
  auto_renewing BOOLEAN,
  status ENUM('pending', 'verified', 'expired', 'cancelled', 'refunded'),
  verified_at TIMESTAMP,
  cancelled_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### 3. Product IDs

Cấu hình trong `GooglePlayBillingController.php`:

```php
private function getProductPrice(string $productId): float
{
    $prices = [
        'moneys_pro_yearly' => 10.00,  // $10/year
        'moneys_pro_monthly' => 1.00,   // $1/month (if you add)
    ];

    return $prices[$productId] ?? 10.00;
}
```

---

## Setup Google Play Console

### 1. Create Service Account

1. Vào [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project hoặc tạo mới
3. Enable **Google Play Android Developer API**
4. Tạo Service Account:
   - Navigation menu → IAM & Admin → Service Accounts
   - Create Service Account
   - Name: `moneys-backend`
   - Grant access to project → Done
5. Create Key:
   - Click vào service account vừa tạo
   - Keys tab → Add Key → Create new key
   - JSON format
   - Download file JSON (đây là `GOOGLE_PLAY_CREDENTIALS_PATH`)

### 2. Link Service Account to Play Console

1. Vào [Google Play Console](https://play.google.com/console/)
2. Users and permissions → Invite new users
3. Nhập email của service account (có dạng `xxx@xxx.iam.gserviceaccount.com`)
4. Permissions → Financial data → View only (Read-only)
5. Send invitation

### 3. Create Subscription Products

1. Play Console → Your App → Monetize → Subscriptions
2. Create subscription:
   - **Product ID**: `moneys_pro_yearly`
   - **Name**: MoneyS PRO - Yearly
   - **Description**: Upgrade to PRO for unlimited subscriptions and features
   - **Price**: $10.00/year
   - **Billing period**: 1 year
   - **Free trial**: Optional (e.g., 7 days)
3. Save and activate

---

## API Endpoints

### 1. Verify Purchase

**POST** `/api/v1/billing/google-play/verify`

Verify Google Play purchase và upgrade user lên PRO.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "product_id": "moneys_pro_yearly",
  "purchase_token": "ghhcncflffegjhpmobgdhcda.AO-J1Ozy...",
  "order_id": "GPA.1234-5678-9012-34567",
  "purchase_time": 1696867200000,
  "receipt_data": {
    "orderId": "GPA.1234-5678-9012-34567",
    "packageName": "com.moneys.app",
    "productId": "moneys_pro_yearly",
    "purchaseTime": 1696867200000,
    "purchaseState": 0,
    "purchaseToken": "ghhcncflffegjhpmobgdhcda.AO-J1Ozy...",
    "autoRenewing": true,
    "acknowledged": false
  }
}
```

**Response 201 (Success):**
```json
{
  "message": "Purchase verified successfully",
  "purchase": {
    "id": "9d5e...",
    "user_id": "9d5e...",
    "product_id": "moneys_pro_yearly",
    "order_id": "GPA.1234-5678-9012-34567",
    "status": "verified",
    "amount": "10.00",
    "currency": "USD",
    "purchased_at": "2025-10-10T00:00:00.000000Z",
    "expires_at": "2026-10-10T00:00:00.000000Z",
    "auto_renewing": true
  },
  "user": {
    "id": "9d5e...",
    "email": "user@example.com",
    "subscription_tier": "pro",
    "subscription_expires_at": "2026-10-10T00:00:00.000000Z"
  }
}
```

**Response 400 (Already Processed):**
```json
{
  "error": "Purchase already processed",
  "message": "This purchase has already been verified.",
  "purchase": { ... }
}
```

**Response 400 (Invalid Purchase):**
```json
{
  "error": "Invalid purchase",
  "message": "Could not verify purchase with Google Play."
}
```

---

### 2. Get Purchase History

**GET** `/api/v1/billing/purchases`

Lấy lịch sử mua hàng của user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
[
  {
    "id": "9d5e...",
    "product_id": "moneys_pro_yearly",
    "order_id": "GPA.1234-5678-9012-34567",
    "status": "verified",
    "amount": "10.00",
    "currency": "USD",
    "purchased_at": "2025-10-10T00:00:00.000000Z",
    "expires_at": "2026-10-10T00:00:00.000000Z",
    "auto_renewing": true,
    "created_at": "2025-10-10T00:05:00.000000Z"
  }
]
```

---

### 3. Get Active Subscription

**GET** `/api/v1/billing/active-subscription`

Kiểm tra subscription đang active.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200 (Has Active):**
```json
{
  "has_active": true,
  "subscription": {
    "id": "9d5e...",
    "product_id": "moneys_pro_yearly",
    "expires_at": "2026-10-10T00:00:00.000000Z",
    "auto_renewing": true,
    "status": "verified"
  }
}
```

**Response 200 (No Active):**
```json
{
  "message": "No active subscription",
  "has_active": false
}
```

---

## Mobile App Integration

### Android (Flutter Example)

#### 1. Add Dependencies

```yaml
# pubspec.yaml
dependencies:
  in_app_purchase: ^3.1.11
  http: ^1.1.0
```

#### 2. Setup Billing Client

```dart
import 'package:in_app_purchase/in_app_purchase.dart';

class BillingService {
  final InAppPurchase _iap = InAppPurchase.instance;

  // Product IDs
  static const String productIdYearly = 'moneys_pro_yearly';

  Future<void> initialize() async {
    final bool available = await _iap.isAvailable();
    if (!available) {
      throw Exception('In-app purchases not available');
    }

    // Listen to purchase updates
    _iap.purchaseStream.listen(_onPurchaseUpdate);
  }

  Future<List<ProductDetails>> getProducts() async {
    final ProductDetailsResponse response = await _iap.queryProductDetails(
      {productIdYearly},
    );

    if (response.error != null) {
      throw Exception('Failed to load products: ${response.error}');
    }

    return response.productDetails;
  }

  Future<void> buyProduct(ProductDetails product) async {
    final PurchaseParam purchaseParam = PurchaseParam(
      productDetails: product,
    );

    await _iap.buyNonConsumable(purchaseParam: purchaseParam);
  }

  void _onPurchaseUpdate(List<PurchaseDetails> purchases) {
    for (var purchase in purchases) {
      if (purchase.status == PurchaseStatus.purchased) {
        // Verify with backend
        _verifyPurchase(purchase);
      } else if (purchase.status == PurchaseStatus.error) {
        // Handle error
        print('Purchase error: ${purchase.error}');
      }

      // Complete purchase
      if (purchase.pendingCompletePurchase) {
        _iap.completePurchase(purchase);
      }
    }
  }

  Future<void> _verifyPurchase(PurchaseDetails purchase) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/billing/google-play/verify'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'product_id': purchase.productID,
          'purchase_token': purchase.verificationData.serverVerificationData,
          'order_id': purchase.purchaseID ?? '',
          'purchase_time': DateTime.now().millisecondsSinceEpoch,
          'receipt_data': {
            'orderId': purchase.purchaseID,
            'productId': purchase.productID,
            'purchaseTime': DateTime.now().millisecondsSinceEpoch,
            'purchaseToken': purchase.verificationData.serverVerificationData,
          },
        }),
      );

      if (response.statusCode == 201) {
        final data = jsonDecode(response.body);
        print('Purchase verified: ${data['message']}');
        // Update local user state
        // Show success message
      } else {
        print('Verification failed: ${response.body}');
      }
    } catch (e) {
      print('Error verifying purchase: $e');
    }
  }
}
```

#### 3. UI Flow

```dart
class UpgradeScreen extends StatefulWidget {
  @override
  _UpgradeScreenState createState() => _UpgradeScreenState();
}

class _UpgradeScreenState extends State<UpgradeScreen> {
  final BillingService _billing = BillingService();
  List<ProductDetails> _products = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    try {
      await _billing.initialize();
      final products = await _billing.getProducts();
      setState(() {
        _products = products;
        _loading = false;
      });
    } catch (e) {
      print('Error loading products: $e');
      setState(() => _loading = false);
    }
  }

  Future<void> _purchaseProduct(ProductDetails product) async {
    try {
      await _billing.buyProduct(product);
    } catch (e) {
      print('Purchase error: $e');
      // Show error to user
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Center(child: CircularProgressIndicator());
    }

    return ListView.builder(
      itemCount: _products.length,
      itemBuilder: (context, index) {
        final product = _products[index];
        return Card(
          child: ListTile(
            title: Text(product.title),
            subtitle: Text(product.description),
            trailing: ElevatedButton(
              onPressed: () => _purchaseProduct(product),
              child: Text(product.price),
            ),
          ),
        );
      },
    );
  }
}
```

---

## Testing

### Development Mode (Without Google Play Credentials)

Backend sẽ tự động skip verification nếu không có credentials:

```bash
# Test verify purchase (sẽ pass mà không verify thật)
curl -X POST http://localhost:8000/api/v1/billing/google-play/verify \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": "moneys_pro_yearly",
    "purchase_token": "test_token_123",
    "order_id": "TEST.1234-5678-9012",
    "purchase_time": 1696867200000
  }'
```

### Test with Google Play Test Account

1. Add test account in Play Console:
   - Setup → License testing
   - Add email addresses for testing
   - Test accounts sẽ không bị charge

2. Build signed APK và upload lên Internal Testing track

3. Install app từ Play Store với test account

4. Purchase sẽ show "Test purchase" dialog

---

## Production Deployment

### 1. Upload Service Account Credentials

```bash
# Upload credentials to server
scp google-play-service-account.json server:/var/www/moneys/storage/

# Update .env
GOOGLE_PLAY_CREDENTIALS_PATH=/var/www/moneys/storage/google-play-service-account.json
```

### 2. Secure Credentials

```bash
# Set proper permissions
chmod 600 /var/www/moneys/storage/google-play-service-account.json
chown www-data:www-data /var/www/moneys/storage/google-play-service-account.json
```

### 3. Monitor Purchases

Admin có thể xem purchases trong Filament admin panel hoặc query database:

```sql
SELECT * FROM purchases
ORDER BY created_at DESC
LIMIT 100;
```

### 4. Handle Subscription Renewals

Google Play tự động renew subscriptions. Backend cần:

1. Listen to Google Play Real-time Developer Notifications (RTDN)
2. Update purchase `expires_at` khi renew
3. Downgrade user nếu subscription expire hoặc cancelled

---

## Error Handling

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| `Purchase already processed` | Duplicate order_id | Ignore, purchase đã được verify |
| `Invalid purchase` | Token không hợp lệ | Check credentials và package name |
| `Verification failed` | Google API error | Check logs, retry |
| `GOOGLE_PLAY_CREDENTIALS_PATH not found` | Missing credentials | Upload service account JSON |

### Logs

Check Laravel logs:

```bash
tail -f storage/logs/laravel.log | grep "Google Play"
```

---

## Admin Panel

Admin có thể quản lý purchases tại `/admin/purchases` (sau khi tạo Filament resource).

---

## Security Best Practices

1. ✅ **Always verify on backend** - Never trust client data
2. ✅ **Store purchase tokens securely** - Use encrypted database
3. ✅ **Log all transactions** - For audit trail
4. ✅ **Rate limit verify endpoint** - Prevent abuse
5. ✅ **Handle edge cases** - Refunds, cancellations, expired subscriptions

---

## Next Steps

1. Create Filament Purchase Resource cho admin panel
2. Add webhook để listen Google Play RTDN
3. Add cron job để check expired subscriptions
4. Add email notifications khi subscription sắp hết hạn
5. Add analytics tracking cho purchases

---

## Support

Nếu gặp vấn đề:
1. Check Laravel logs
2. Check Google Play Console → Order management
3. Verify service account permissions
4. Test with sandbox account first
