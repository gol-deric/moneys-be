# Firebase Cloud Messaging (FCM) - MoneyS

## Tổng quan

Hệ thống FCM notification của MoneyS cho phép:
- **Users**: Đăng ký nhiều thiết bị (FCM tokens)
- **System**: Tự động gửi thông báo nhắc nhở subscription renewal
- **Admins**: Gửi custom notifications từ admin panel

---

## Features

### ✅ User Device Management
- 1 user có thể có nhiều devices (phone, tablet, web)
- Auto check duplicate FCM token khi đăng ký
- Reassign token nếu user khác logout và user mới login
- Track device info: type (android/ios/web), name, app version
- Active/Inactive status
- Last used timestamp

### ✅ Admin Notification System
- **Send to Single User**: Gửi đến 1 user cụ thể
- **Send to Multiple Users**: Chọn nhiều users
- **Broadcast to All**: Gửi đến tất cả users
- Live preview notification
- Success/Failed tracking

### ✅ Auto Notifications
- Subscription renewal reminders (7, 3, 1 days before)
- Payment due notifications
- Subscription expired alerts

---

## API Endpoints

### User Endpoints

#### 1. Register FCM Token

**POST** `/api/v1/device-tokens/register`

Đăng ký FCM token cho device. Tự động check duplicate và reassign nếu cần.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "fcm_token": "eXEr5Y8h9R0:APA91bF...",
  "device_type": "android",
  "device_name": "Samsung Galaxy S21",
  "app_version": "1.0.0"
}
```

**Response 201 (New Token):**
```json
{
  "message": "FCM token registered successfully",
  "device_token": {
    "id": "9d5e...",
    "user_id": "9d5e...",
    "fcm_token": "eXEr5Y8h9R0:APA91bF...",
    "device_type": "android",
    "device_name": "Samsung Galaxy S21",
    "app_version": "1.0.0",
    "is_active": true,
    "last_used_at": "2025-10-10T16:00:00.000000Z",
    "created_at": "2025-10-10T16:00:00.000000Z"
  }
}
```

**Response 200 (Token Already Exists):**
```json
{
  "message": "FCM token updated successfully",
  "device_token": { ... }
}
```

---

#### 2. Get My Device Tokens

**GET** `/api/v1/device-tokens`

**Response:**
```json
[
  {
    "id": "9d5e...",
    "fcm_token": "eXEr5Y8h9R0:APA91bF...",
    "device_type": "android",
    "device_name": "Samsung Galaxy S21",
    "app_version": "1.0.0",
    "is_active": true,
    "last_used_at": "2025-10-10T16:00:00.000000Z"
  },
  {
    "id": "9d5f...",
    "fcm_token": "fYFs6Z9i0S1:APA91bG...",
    "device_type": "ios",
    "device_name": "iPhone 14 Pro",
    "app_version": "1.0.0",
    "is_active": true,
    "last_used_at": "2025-10-09T10:30:00.000000Z"
  }
]
```

---

#### 3. Delete Device Token

**DELETE** `/api/v1/device-tokens/{id}`

**Response:**
```json
{
  "message": "Device token deleted successfully"
}
```

---

#### 4. Delete by FCM Token String

**DELETE** `/api/v1/device-tokens/by-token/delete`

**Request Body:**
```json
{
  "fcm_token": "eXEr5Y8h9R0:APA91bF..."
}
```

---

#### 5. Deactivate Token

**PATCH** `/api/v1/device-tokens/{id}/deactivate`

Deactivate token nhưng không xóa (soft delete).

---

### Admin Endpoints (Require Admin Permission)

#### 1. Send to Single User

**POST** `/api/v1/admin/notifications/send-to-user/{userId}`

**Request:**
```json
{
  "title": "Special Offer!",
  "body": "Upgrade to PRO and get 20% off!",
  "data": {
    "action": "upgrade",
    "discount": "20"
  }
}
```

**Response:**
```json
{
  "message": "Notifications sent",
  "total_devices": 2,
  "success_count": 2,
  "failed_count": 0,
  "failed_tokens": []
}
```

---

#### 2. Send to All Users

**POST** `/api/v1/admin/notifications/send-to-all`

**Request:**
```json
{
  "title": "New Feature Released!",
  "body": "Check out our new analytics dashboard.",
  "data": {
    "action": "open_analytics"
  }
}
```

---

#### 3. Send to Multiple Users

**POST** `/api/v1/admin/notifications/send-to-users`

**Request:**
```json
{
  "user_ids": ["9d5e...", "9d5f...", "9d60..."],
  "title": "Payment Reminder",
  "body": "Your subscription will expire in 3 days.",
  "data": {
    "action": "renew_subscription"
  }
}
```

---

## Admin Panel Features

### 1. Send Notification Page

Navigate to **Communications → Send Notification**

**Features:**
- ✅ Title & Message input
- ✅ Choose recipient type:
  - All Users
  - Specific Users (multi-select)
  - Single User (searchable)
- ✅ Live notification preview
- ✅ Success/Failed count tracking

**Screenshots:**
```
┌─────────────────────────────────────────┐
│ Notification Content                    │
├─────────────────────────────────────────┤
│ Title: [New Feature!               ]    │
│ Message:                                │
│ ┌─────────────────────────────────────┐ │
│ │ Check out our new dashboard!       │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ Recipients                              │
├─────────────────────────────────────────┤
│ ○ All Users                             │
│ ○ Specific Users                        │
│ ○ Single User                           │
├─────────────────────────────────────────┤
│ Preview                                 │
├─────────────────────────────────────────┤
│ ┌─ 🔔 ─────────────────────────────┐   │
│ │ New Feature!                     │   │
│ │ Check out our new dashboard!     │   │
│ │ Just now                         │   │
│ └──────────────────────────────────┘   │
├─────────────────────────────────────────┤
│ [📤 Send Notification]                  │
└─────────────────────────────────────────┘
```

---

### 2. Device Tokens Management

Navigate to **Communications → Device Tokens**

**Features:**
- ✅ View all FCM tokens
- ✅ Filter by device type (Android/iOS/Web)
- ✅ Filter by active status
- ✅ Sort by last used time
- ✅ Deactivate/Activate tokens
- ✅ Delete tokens

**Columns:**
- User (searchable)
- Device Type (badge with color)
- Device Name
- App Version
- Active Status
- Last Used (relative time)

---

### 3. User Detail → Device Tokens Tab

Navigate to **User Management → Users → [User] → Device Tokens**

**Features:**
- ✅ View all devices của user cụ thể
- ✅ See FCM token (copyable)
- ✅ Deactivate/Activate per device
- ✅ Delete device
- ✅ Last used tracking

---

## Mobile App Integration

### Flutter Example

#### 1. Setup Firebase

```yaml
# pubspec.yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.9
  http: ^1.1.0
```

#### 2. Initialize Firebase

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();

  // Request permission
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  NotificationSettings settings = await messaging.requestPermission(
    alert: true,
    badge: true,
    sound: true,
  );

  if (settings.authorizationStatus == AuthorizationStatus.authorized) {
    print('User granted permission');
  }

  runApp(MyApp());
}
```

#### 3. Register FCM Token

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class FCMService {
  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;

  Future<void> registerToken(String authToken) async {
    try {
      // Get FCM token
      String? fcmToken = await _firebaseMessaging.getToken();

      if (fcmToken == null) {
        print('Failed to get FCM token');
        return;
      }

      print('FCM Token: $fcmToken');

      // Send to backend
      final response = await http.post(
        Uri.parse('$baseUrl/api/v1/device-tokens/register'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'fcm_token': fcmToken,
          'device_type': Platform.isAndroid ? 'android' : 'ios',
          'device_name': await _getDeviceName(),
          'app_version': await _getAppVersion(),
        }),
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        print('FCM token registered successfully');
      } else {
        print('Failed to register: ${response.body}');
      }
    } catch (e) {
      print('Error registering FCM token: $e');
    }
  }

  Future<String> _getDeviceName() async {
    // Use device_info_plus package
    final deviceInfo = DeviceInfoPlugin();
    if (Platform.isAndroid) {
      final androidInfo = await deviceInfo.androidInfo;
      return '${androidInfo.manufacturer} ${androidInfo.model}';
    } else {
      final iosInfo = await deviceInfo.iosInfo;
      return '${iosInfo.name} ${iosInfo.model}';
    }
  }

  Future<String> _getAppVersion() async {
    final packageInfo = await PackageInfo.fromPlatform();
    return packageInfo.version;
  }

  Future<void> unregisterToken(String authToken, String fcmToken) async {
    await http.delete(
      Uri.parse('$baseUrl/api/v1/device-tokens/by-token/delete'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'fcm_token': fcmToken}),
    );
  }
}
```

#### 4. Listen to Notifications

```dart
class NotificationHandler {
  static void initialize() {
    // Foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print('Got a message whilst in the foreground!');
      print('Message data: ${message.data}');

      if (message.notification != null) {
        print('Message also contained a notification: ${message.notification}');
        _showNotification(message.notification!);
      }
    });

    // Background messages
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      print('A new onMessageOpenedApp event was published!');
      _handleNotificationClick(message.data);
    });

    // Terminated state
    FirebaseMessaging.instance.getInitialMessage().then((message) {
      if (message != null) {
        _handleNotificationClick(message.data);
      }
    });
  }

  static void _showNotification(RemoteNotification notification) {
    // Show local notification using flutter_local_notifications
    // ...
  }

  static void _handleNotificationClick(Map<String, dynamic> data) {
    final action = data['action'];

    switch (action) {
      case 'upgrade':
        // Navigate to upgrade screen
        break;
      case 'renew_subscription':
        // Navigate to subscription renewal
        break;
      case 'open_analytics':
        // Navigate to analytics
        break;
    }
  }
}
```

#### 5. App Lifecycle

```dart
class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> with WidgetsBindingObserver {
  final FCMService _fcmService = FCMService();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    // Register token when app starts
    _registerFCMToken();

    // Initialize notification handler
    NotificationHandler.initialize();
  }

  Future<void> _registerFCMToken() async {
    final authToken = await getAuthToken(); // Your auth method
    if (authToken != null) {
      await _fcmService.registerToken(authToken);
    }
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      // Re-register token when app comes to foreground
      _registerFCMToken();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: HomeScreen(),
    );
  }
}
```

---

## Testing

### Test với Postman/cURL

```bash
# 1. Register a test token
curl -X POST http://localhost:8000/api/v1/device-tokens/register \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "fcm_token": "test_fcm_token_123",
    "device_type": "android",
    "device_name": "Test Device",
    "app_version": "1.0.0"
  }'

# 2. Send notification from admin (login as admin first)
curl -X POST http://localhost:8000/api/v1/admin/notifications/send-to-all \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Notification",
    "body": "This is a test message"
  }'
```

### Test trong Admin Panel

1. Login vào admin panel: `/admin`
2. Navigate to **Communications → Send Notification**
3. Fill form:
   - Title: "Test Notification"
   - Message: "This is a test"
   - Recipient: "All Users"
4. Click "Send Notification"
5. Check console logs hoặc Firebase Console

---

## Firebase Setup

### 1. Create Firebase Project

1. Vào [Firebase Console](https://console.firebase.google.com/)
2. Create new project: "MoneyS"
3. Add Android/iOS app
4. Download `google-services.json` (Android) và `GoogleService-Info.plist` (iOS)

### 2. Get Server Key

1. Firebase Console → Project Settings → Cloud Messaging
2. Copy **Server key**
3. Thêm vào `.env`:

```env
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
```

### 3. Service Account

1. Firebase Console → Project Settings → Service Accounts
2. Generate new private key
3. Save JSON file
4. Update `.env` path

---

## Best Practices

### 1. Token Management

✅ **DO:**
- Register token every time app opens
- Unregister token on logout
- Handle token refresh automatically
- Check for duplicate tokens

❌ **DON'T:**
- Store tokens in SharedPreferences permanently
- Send same token multiple times without checking
- Forget to unregister on logout

### 2. Notification Handling

✅ **DO:**
- Handle foreground, background, and terminated states
- Use data payload for custom actions
- Show local notification in foreground
- Track notification open rate

❌ **DON'T:**
- Send notifications too frequently
- Send large data payloads
- Ignore notification permissions

### 3. Admin Usage

✅ **DO:**
- Preview notification before sending
- Test with single user first
- Monitor success/failed counts
- Keep messages concise and clear

❌ **DON'T:**
- Spam users with notifications
- Send marketing without consent
- Use ALL CAPS or excessive emojis

---

## Troubleshooting

### Token not registering

**Problem**: FCM token không được gửi lên backend

**Solutions:**
1. Check Firebase initialization
2. Verify permission granted
3. Check network connection
4. Verify auth token valid

### Notifications not received

**Problem**: Không nhận được notification

**Solutions:**
1. Check token is active trong admin
2. Verify Firebase credentials
3. Check app is in foreground/background
4. Test với Firebase Console Test Message

### Duplicate tokens

**Problem**: Nhiều tokens cho same device

**Solutions:**
- Backend tự động handle duplicate
- Unregister old token on logout
- Register new token on login

---

## Summary

✅ **Completed Features:**
- Device token registration với duplicate check
- Admin send notification UI trong Filament
- View device tokens trong user detail
- API endpoints đầy đủ
- Auto deactivate failed tokens
- Track last used timestamp

🎉 **Ready to use!**

Admin có thể vào **Communications → Send Notification** để gửi notification ngay bây giờ!
