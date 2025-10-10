# MoneyS - Subscription Tiers

## 🆓 FREE Tier (Miễn phí)

### Giới hạn cơ bản:
- **Số lượng subscriptions**: Tối đa **5 subscriptions**
- **Lịch sử**: Giữ lịch sử **30 ngày**
- **Thông báo**: Nhắc nhở **1 ngày trước** hạn thanh toán
- **Xuất dữ liệu**: Không có tính năng export

### Tính năng FREE:
✅ Quản lý tối đa 5 subscriptions
✅ Xem tổng chi phí hàng tháng
✅ Lịch thanh toán cơ bản
✅ Thông báo push 1 ngày trước hạn
✅ Giao diện dark/light mode
✅ Đồng bộ đám mây cơ bản
✅ Hỗ trợ nhiều loại tiền tệ
✅ Widget calendar view

### Hạn chế:
❌ Không có thống kê chi tiết
❌ Không export PDF/CSV
❌ Không có báo cáo phân tích
❌ Không nhắc nhở nhiều lần
❌ Không có lịch sử lâu dài

---

## 💎 PRO Tier (Trả phí)

### Giá: **$4.99/tháng** hoặc **$49.99/năm** (tiết kiệm 17%)

### Không giới hạn:
- **Số lượng subscriptions**: **Không giới hạn**
- **Lịch sử**: **Vô thời hạn**
- **Thông báo**: **3 lần nhắc nhở** (7 ngày, 3 ngày, 1 ngày trước)
- **Xuất dữ liệu**: Export PDF và CSV

### Tính năng PRO:
✅ **Không giới hạn subscriptions**
✅ **Thông báo thông minh**: 7, 3, 1 ngày trước
✅ **Thống kê chi tiết**:
  - Biểu đồ xu hướng chi tiêu
  - Phân tích theo category
  - So sánh theo tháng/năm
  - Top subscriptions tốn kém nhất
✅ **Export dữ liệu**:
  - Export PDF báo cáo
  - Export CSV cho Excel
  - Email báo cáo hàng tháng
✅ **Lịch sử vô thời hạn**
✅ **Widget nâng cao**:
  - Widget thống kê
  - Widget upcoming payments
  - Widget spending chart
✅ **Ưu tiên hỗ trợ khách hàng**
✅ **Backup tự động hàng ngày**
✅ **Tags và Categories tùy chỉnh**
✅ **Sharing subscriptions** (gia đình/nhóm)

---

## 📊 So sánh tính năng

| Tính năng | FREE | PRO |
|-----------|------|-----|
| Số lượng subscriptions | 5 | ∞ Không giới hạn |
| Lịch sử | 30 ngày | Vô thời hạn |
| Thông báo | 1 ngày trước | 7, 3, 1 ngày trước |
| Thống kê cơ bản | ✅ | ✅ |
| Biểu đồ & phân tích | ❌ | ✅ |
| Export PDF/CSV | ❌ | ✅ |
| Email báo cáo | ❌ | ✅ |
| Custom categories | ❌ | ✅ |
| Widget nâng cao | ❌ | ✅ |
| Sharing subscriptions | ❌ | ✅ |
| Backup tự động | ❌ | ✅ |
| Ưu tiên support | ❌ | ✅ |

---

## 🔧 Implementation Notes

### Database Changes Needed:

#### 1. Update `users` table migration:
```php
// Remove 'enterprise' tier, keep only 'free' and 'pro'
$table->enum('subscription_tier', ['free', 'pro'])->default('free');
```

#### 2. Create `tier_limits` config:
```php
// config/tier_limits.php
return [
    'free' => [
        'max_subscriptions' => 5,
        'history_days' => 30,
        'notification_days' => [1], // 1 day before
        'export_enabled' => false,
        'advanced_stats' => false,
        'custom_categories' => false,
        'sharing_enabled' => false,
    ],
    'pro' => [
        'max_subscriptions' => null, // unlimited
        'history_days' => null, // unlimited
        'notification_days' => [7, 3, 1], // 7, 3, 1 days before
        'export_enabled' => true,
        'advanced_stats' => true,
        'custom_categories' => true,
        'sharing_enabled' => true,
    ],
];
```

#### 3. Add Middleware để check limits:
```php
// app/Http/Middleware/CheckSubscriptionLimit.php
public function handle(Request $request, Closure $next)
{
    $user = $request->user();
    $limits = config("tier_limits.{$user->subscription_tier}");

    if ($limits['max_subscriptions'] !== null) {
        $count = $user->subscriptions()->count();
        if ($count >= $limits['max_subscriptions']) {
            return response()->json([
                'error' => 'Subscription limit reached',
                'message' => 'Upgrade to PRO for unlimited subscriptions',
                'current_tier' => $user->subscription_tier,
                'limit' => $limits['max_subscriptions'],
            ], 403);
        }
    }

    return $next($request);
}
```

### API Changes:

#### 4. Add endpoint để check tier limits:
```php
// GET /api/v1/users/tier-info
public function tierInfo(Request $request): JsonResponse
{
    $user = $request->user();
    $tier = $user->subscription_tier;
    $limits = config("tier_limits.{$tier}");
    $currentCount = $user->subscriptions()->count();

    return response()->json([
        'current_tier' => $tier,
        'limits' => $limits,
        'usage' => [
            'subscriptions' => [
                'current' => $currentCount,
                'max' => $limits['max_subscriptions'] ?? 'unlimited',
                'percentage' => $limits['max_subscriptions']
                    ? ($currentCount / $limits['max_subscriptions'] * 100)
                    : 0,
            ],
        ],
        'can_upgrade' => $tier === 'free',
    ]);
}
```

#### 5. Add upgrade endpoint:
```php
// POST /api/v1/users/upgrade-to-pro
public function upgradeToPro(Request $request): JsonResponse
{
    $user = $request->user();

    if ($user->subscription_tier === 'pro') {
        return response()->json(['error' => 'Already on PRO tier'], 400);
    }

    // TODO: Integrate with payment gateway (Stripe, PayPal, etc.)
    // For now, just upgrade directly

    $user->update([
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addMonth(), // or addYear()
    ]);

    return response()->json([
        'message' => 'Successfully upgraded to PRO',
        'user' => $user,
    ]);
}
```

---

## 🎯 Conversion Strategy

### Khi nào hiển thị upgrade prompt:

1. **Đạt 4/5 subscriptions**: "Bạn đã dùng 4/5 subscriptions. Upgrade để thêm không giới hạn!"
2. **Thêm subscription thứ 6**: Block và hiện modal upgrade
3. **Cố export dữ liệu**: "Tính năng này chỉ dành cho PRO"
4. **Xem thống kê nâng cao**: Show locked UI với nút "Unlock with PRO"
5. **Sau 30 ngày**: "Lịch sử cũ sẽ bị xóa. Upgrade để giữ mãi mãi!"

### UI Elements:

- Badge "PRO" trên các tính năng premium
- Màn hình so sánh khi tap "Upgrade"
- Trial 7 ngày miễn phí cho PRO
- Giảm giá cho gói năm (tiết kiệm 17%)

---

## 💰 Monetization Model

### Pricing:
- **Monthly**: $4.99/tháng
- **Yearly**: $49.99/năm (= $4.16/tháng, tiết kiệm $9.89)
- **Lifetime** (optional): $99.99 một lần

### Payment Integration:
- In-App Purchase (iOS/Android)
- Stripe for web
- PayPal support

### Free Trial:
- 7 ngày dùng thử PRO miễn phí
- Tự động chuyển về FREE nếu không thanh toán
- Không cần thẻ tín dụng để dùng thử
