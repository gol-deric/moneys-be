# MoneyS - Admin Panel Guide

## Truy cập Admin Panel

URL: **http://localhost:8000/admin**

### Tài khoản mặc định:
- **Email**: admin@moneys.com
- **Password**: password

## Tính năng giao diện quản lý

### 1. Dashboard (Trang chủ)
Khi đăng nhập, bạn sẽ thấy trang Dashboard với các thống kê quan trọng:

#### Stats Overview Widget
- **Total Users**: Tổng số người dùng (phân biệt regular/guest)
- **Active Subscriptions**: Số subscription đang hoạt động (hiển thị số bị cancelled)
- **Monthly Revenue**: Doanh thu hàng tháng từ các subscription đang active
- **Unread Notifications**: Số thông báo chưa đọc

### 2. Quản lý Users (User Management)

#### 📊 Bảng danh sách
Hiển thị thông tin:
- Full Name (có thể tìm kiếm)
- Email (có thể tìm kiếm)
- Is Guest (biểu tượng ✓/✗)
- Subscription Tier (badge màu: gray=free, yellow=premium, green=enterprise)
- Subscriptions Count (số lượng subscription của user)
- Created At (ngày tạo)

#### 🔍 Filters
- **Subscription Tier**: Lọc theo gói (Free/Premium/Enterprise)
- **Guest Users**: Chỉ hiển thị tài khoản khách

#### ✏️ Form tạo/sửa
- Email (bắt buộc, unique)
- Full Name (bắt buộc)
- Is Guest (toggle on/off)
- Subscription Tier (select: Free/Premium/Enterprise)
- Theme (select: Light/Dark)
- Notifications Enabled (toggle)

### 3. Quản lý Subscriptions (Subscription Management)

#### 📊 Bảng danh sách
Hiển thị:
- User (tên người dùng, có thể tìm kiếm)
- Name (tên subscription, có thể tìm kiếm)
- Price (giá với đơn vị tiền tệ)
- Category (danh mục với badge)
- Is Cancelled (biểu tượng ✓/✗)
- Start Date (ngày bắt đầu)

#### 🔍 Filters
- **Category**: Lọc theo danh mục (dynamic từ database)
- **Cancelled**: Chỉ hiển thị subscription đã hủy

#### ✏️ Form tạo/sửa
- User ID (select có search, bắt buộc)
- Name (tên subscription, bắt buộc)
- Icon URL (đường dẫn icon)
- Price (số tiền, min=0, bắt buộc)
- Currency Code (3 ký tự, mặc định USD)
- Start Date (ngày bắt đầu, mặc định hôm nay)
- Billing Cycle Count (số chu kỳ, min=1, mặc định 1)
- Billing Cycle Period (select: Day/Month/Quarter/Year, mặc định Month)
- Category (danh mục)
- Notes (ghi chú, textarea)
- Is Cancelled (toggle, mặc định false)

### 4. Quản lý Payment Plans

Icon: 💲 (Currency Dollar)

#### Chức năng
- Quản lý các gói thanh toán (Free/Premium/Enterprise)
- Cấu hình giá, chu kỳ thanh toán
- Quản lý features (JSON)
- Giới hạn số subscription tối đa
- Bật/tắt gói

### 5. Quản lý Notifications (Communication)

Icon: 🔔 (Bell)

#### Chức năng
- Xem tất cả thông báo trong hệ thống
- Theo dõi thông báo đã đọc/chưa đọc
- Quản lý thông báo renewal/payment/system

## Tính năng UI/UX

### Navigation Groups
Sidebar được chia thành 3 nhóm:
1. **User Management** (Quản lý người dùng)
   - Users

2. **Subscription Management** (Quản lý subscription)
   - Subscriptions
   - Payment Plans

3. **Communication** (Giao tiếp)
   - Notifications

### Responsive Design
- Sidebar có thể thu gọn trên desktop
- Tự động responsive trên mobile
- Dark mode support (theo theme của user)

### Color Scheme
- **Primary**: Green (màu chủ đạo)
- **Success**: Green (thành công)
- **Warning**: Amber (cảnh báo)
- **Danger**: Red (lỗi)

### Icons (Heroicons)
- Users: 👥 (users)
- Subscriptions: 💳 (credit-card)
- Payment Plans: 💲 (currency-dollar)
- Notifications: 🔔 (bell)

## Thao tác thông dụng

### Tạo User mới
1. Vào **User Management** → **Users**
2. Click **New User**
3. Điền thông tin (email, full name)
4. Chọn subscription tier
5. Click **Create**

### Tạo Subscription cho User
1. Vào **Subscription Management** → **Subscriptions**
2. Click **New Subscription**
3. Chọn User từ dropdown (có search)
4. Điền thông tin subscription:
   - Tên (vd: Netflix, Spotify)
   - Giá và đơn vị tiền tệ
   - Ngày bắt đầu
   - Chu kỳ thanh toán
   - Danh mục
5. Click **Create**

### Xem thống kê
- Dashboard hiển thị thống kê real-time
- Số liệu tự động cập nhật từ database
- Badge màu giúp phân biệt trạng thái

### Tìm kiếm & Lọc
- Dùng search box phía trên bảng
- Click **Filters** để lọc theo điều kiện
- Combine nhiều filter cùng lúc

### Bulk Actions
- Chọn nhiều records bằng checkbox
- Actions menu xuất hiện (Delete, Export, etc.)

## Bảo mật

### Authentication
- Sử dụng Laravel Sanctum cho API
- Filament sử dụng session-based auth
- CSRF protection enabled

### Authorization
- Chỉ authenticated users mới truy cập được admin panel
- Có thể mở rộng với Policies/Permissions sau

## Tips & Tricks

### 1. Export Data
Filament hỗ trợ export dữ liệu sang Excel/CSV:
- Chọn records cần export
- Click **Export** trong bulk actions

### 2. Soft Deletes
- Users và Subscriptions có soft delete
- Data không bị xóa vĩnh viễn
- Có thể restore sau

### 3. Search Performance
- Search hoạt động trên indexed columns
- Kết quả hiển thị real-time

### 4. Pagination
- Mặc định 15 items/page
- Có thể thay đổi trong code

## Troubleshooting

### Không đăng nhập được
```bash
# Tạo lại admin user
php artisan db:seed --class=AdminUserSeeder
```

### Thống kê không chính xác
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Lỗi 404 khi truy cập /admin
```bash
# Clear route cache
php artisan route:clear
```

## Mở rộng

### Thêm Custom Widget
```php
php artisan make:filament-widget YourWidget
```

### Thêm Custom Page
```php
php artisan make:filament-page YourPage
```

### Thêm Custom Resource
```php
php artisan make:filament-resource YourModel
```

## Screenshots Preview

### Dashboard
- Stats cards với icons đẹp
- Color-coded badges
- Real-time data

### User Management
- Searchable table
- Filter sidebar
- Badge cho subscription tiers

### Subscription Management
- Currency-aware pricing
- Category badges
- Relationship dengan users

---

**Developed with ❤️ using Filament v3**
