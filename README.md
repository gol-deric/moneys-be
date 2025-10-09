# MoneyS - Subscription Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.0-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)

Hệ thống quản lý subscription tracking với Laravel 12 backend API và Filament Admin Panel.

## 🎯 Tính năng chính

### Backend API
- ✅ RESTful API hoàn chỉnh với Laravel Sanctum authentication
- ✅ Quản lý user (register, login, guest accounts, upgrade)
- ✅ Quản lý subscriptions với billing cycle linh hoạt
- ✅ Calendar view theo tháng
- ✅ Thống kê chi tiết (monthly cost, paid/remaining amounts)
- ✅ Push notifications qua Firebase Cloud Messaging
- ✅ Scheduled jobs tự động nhắc nhở renewal
- ✅ Soft deletes và UUID primary keys

### Admin Panel
- ✅ Dashboard với stats widgets real-time
- ✅ Quản lý Users với filters và search
- ✅ Quản lý Subscriptions với category badges
- ✅ Quản lý Payment Plans
- ✅ Quản lý Notifications
- ✅ Navigation groups và icons đẹp
- ✅ Responsive design với sidebar collapsible

## 📦 Cài đặt

### Requirements
- PHP 8.2+
- MySQL 8.0+
- Redis
- Composer
- Node.js & NPM (optional, cho Vite)

### Bước 1: Clone và cài đặt dependencies
```bash
cd /var/www/html/moneys
composer install
```

### Bước 2: Cấu hình .env
```bash
cp .env.example .env
php artisan key:generate
```

Cập nhật các biến sau trong `.env`:
```env
APP_NAME=MoneyS
DB_DATABASE=moneys
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session Configuration (Important!)
SESSION_DRIVER=file
SESSION_LIFETIME=720

QUEUE_CONNECTION=redis

# Firebase credentials
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
```

### Bước 3: Chạy migrations và seed
```bash
php artisan migrate:fresh
php artisan db:seed --class=AdminUserSeeder
```

### Bước 4: Start services
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:work

# Terminal 3: Start scheduler (development)
php artisan schedule:work
```

## 🔑 Truy cập

### Admin Panel
- **URL**: http://localhost:8000/admin
- **Email**: admin@moneys.com
- **Password**: password

### API Endpoints
- **Base URL**: http://localhost:8000/api/v1
- **Documentation**: 📖 [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - **ĐỌC ĐÂY!**
- **Postman Collection**: [MoneyS.postman_collection.json](MoneyS.postman_collection.json)

## 📚 Documentation

### 📖 API & Development
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - 📌 **Complete API reference** (20 endpoints)
- [README_IMPLEMENTATION.md](README_IMPLEMENTATION.md) - Technical implementation details
- [MoneyS.postman_collection.json](MoneyS.postman_collection.json) - Postman collection để test

### 🎨 Admin Panel
- [ADMIN_GUIDE.md](ADMIN_GUIDE.md) - Hướng dẫn sử dụng Admin Panel

### 🔧 Troubleshooting
- [FIX_419_ERROR.md](FIX_419_ERROR.md) - **🔥 Fix lỗi "419 Page Expired"**
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Troubleshooting guide
- [QUICK_FIX.md](QUICK_FIX.md) - Quick fix commands

## 🗂️ Cấu trúc project

```
moneys/
├── app/
│   ├── Filament/
│   │   ├── Resources/          # Admin resources (User, Subscription, etc.)
│   │   └── Widgets/            # Dashboard widgets
│   ├── Http/Controllers/Api/   # API controllers
│   ├── Jobs/                   # Background jobs
│   ├── Models/                 # Eloquent models
│   └── Services/               # Services (Firebase, etc.)
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── routes/
│   ├── api.php                 # API routes
│   ├── console.php             # Console routes & schedules
│   └── web.php                 # Web routes
└── config/
    ├── firebase.php            # Firebase config
    └── filament.php            # Filament config
```

## 🔧 Tech Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament 3.0
- **Authentication**: Laravel Sanctum
- **Queue**: Laravel Horizon + Redis
- **Push Notifications**: Firebase Cloud Messaging
- **Database**: MySQL with UUID primary keys

## 📱 API Examples

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

### Create Subscription
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

## 🎨 Admin Panel Features

### Dashboard Stats
- Total Users (regular/guest breakdown)
- Active Subscriptions (with cancelled count)
- Monthly Revenue (from active subscriptions)
- Unread Notifications

### User Management
- 👤 Full CRUD operations
- 🔍 Search by name/email
- 🏷️ Filter by tier/guest status
- 📊 Subscription count per user

### Subscription Management
- 💳 Full CRUD operations
- 🔍 Search by name/user
- 🏷️ Filter by category/cancelled
- 💰 Currency-aware pricing display

### Navigation Groups
1. **User Management** - Users
2. **Subscription Management** - Subscriptions, Payment Plans
3. **Communication** - Notifications

## ⚡ Scheduled Tasks

Tự động chạy daily:
- **08:00**: Kiểm tra subscriptions due today (0 days)
- **09:00**: Kiểm tra subscriptions due in 3 days
- **09:30**: Kiểm tra subscriptions due tomorrow (1 day)

## 🔐 Security

- Password hashing với bcrypt
- API authentication via Sanctum tokens
- Email validation và uniqueness checks
- Input validation trên tất cả endpoints
- CSRF protection
- Guest account isolation

## 🚀 Production Deployment

### Setup Supervisor cho Queue Worker
```ini
[program:moneys-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/moneys/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/moneys/storage/logs/worker.log
```

### Setup Cron Job
```bash
* * * * * cd /var/www/html/moneys && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter UserTest
```

## 📝 License

Proprietary - All rights reserved

## 👥 Contributors

- Implementation: Claude AI
- Date: October 2025

## 📞 Support

Xem documentation files để biết thêm chi tiết:
- [ADMIN_GUIDE.md](ADMIN_GUIDE.md) - Admin panel guide
- [README_IMPLEMENTATION.md](README_IMPLEMENTATION.md) - Technical implementation

---

Made with ❤️ using Laravel 12 & Filament 3
