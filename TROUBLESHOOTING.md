# MoneyS - Troubleshooting Guide

## Common Issues & Solutions

### 🔥 1. ❌ Error: "419 Page Expired" hoặc "This page has expired. Would you like to refresh?"

**Nguyên nhân**: CSRF token expired do session timeout hoặc cache issues.

**Giải pháp nhanh:**

```bash
# 1. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 2. Clear old sessions
mysql -u username -p database_name -e "DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY));"

# 3. Restart server
# Ctrl + C to stop, then:
php artisan serve
```

**Giải pháp lâu dài - Tăng session lifetime:**

Trong file `.env`:
```env
SESSION_LIFETIME=720  # 12 hours (thay vì 120 = 2 hours)
```

**Hoặc đổi session driver sang file:**
```env
SESSION_DRIVER=file  # Thay vì database
```

Sau đó:
```bash
php artisan config:clear
```

**Tips để tránh lỗi này:**
- ✅ Không để tab browser idle quá lâu
- ✅ Refresh trước khi submit form nếu đã mở lâu
- ✅ Tăng SESSION_LIFETIME trong .env
- ✅ Sử dụng SESSION_DRIVER=file cho development

---

### 2. ❌ Error: "getUserName(): Return value must be of type string, null returned"

**Nguyên nhân**: Filament cố gắng lấy trường `name` từ User model nhưng ta chỉ có `full_name`.

**Giải pháp**: Đã fix bằng cách thêm accessor `getNameAttribute()` vào User model.

**Nếu vẫn lỗi, làm theo các bước sau:**

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Clear sessions
php artisan session:flush

# 3. Restart server
# Ctrl + C để stop, sau đó:
php artisan serve
```

**Kiểm tra User model có đoạn này:**
```php
public function getNameAttribute(): ?string
{
    return $this->full_name;
}
```

---

### 2. ❌ Error: "SQLSTATE[HY000]: General error: 1364 Field 'name' doesn't have a default value"

**Nguyên nhân**: Migration cũ vẫn có cột `name`.

**Giải pháp**:
```bash
# Fresh migrate
php artisan migrate:fresh --seed --seeder=AdminUserSeeder
```

---

### 3. ❌ Cannot access /admin - 404 Error

**Nguyên nhân**: Routes chưa được cache hoặc Filament chưa được cài đúng.

**Giải pháp**:
```bash
# Clear route cache
php artisan route:clear

# Verify Filament routes
php artisan route:list --path=admin

# Reinstall Filament nếu cần
php artisan filament:install --panels
```

---

### 4. ❌ Admin login không hoạt động

**Nguyên nhân**: Không có admin user hoặc sai password.

**Giải pháp**:
```bash
# Tạo lại admin user
php artisan db:seed --class=AdminUserSeeder
```

**Credentials:**
- Email: admin@moneys.com
- Password: password

---

### 5. ❌ Queue worker không chạy

**Nguyên nhân**: Queue connection không đúng hoặc Redis chưa chạy.

**Giải pháp**:
```bash
# Check Redis
redis-cli ping
# Response: PONG

# Restart queue worker
php artisan queue:restart
php artisan queue:work

# Hoặc dùng Horizon
php artisan horizon
```

---

### 6. ❌ Firebase notifications không gửi được

**Nguyên nhân**: Thiếu credentials file hoặc đường dẫn sai.

**Giải pháp**:

1. Check `.env`:
```env
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
```

2. Verify file tồn tại:
```bash
ls -la /path/to/firebase-credentials.json
```

3. Test Firebase connection:
```bash
php artisan tinker
>>> $service = new App\Services\FirebaseService();
>>> # Should not throw error
```

---

### 7. ❌ Stats widget hiển thị số liệu sai

**Nguyên nhân**: Cache hoặc database chưa cập nhật.

**Giải pháp**:
```bash
# Clear cache
php artisan cache:clear

# Refresh database
php artisan migrate:refresh --seed
```

---

### 8. ❌ API endpoints trả về 401 Unauthorized

**Nguyên nhân**: Thiếu hoặc sai token authentication.

**Giải pháp**:

1. Login để lấy token:
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

2. Sử dụng token:
```bash
curl http://localhost:8000/api/v1/users/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

### 9. ❌ Scheduled tasks không chạy

**Nguyên nhân**: Scheduler chưa được setup hoặc cron job chưa chạy.

**Giải pháp**:

**Development:**
```bash
# Terminal riêng cho scheduler
php artisan schedule:work
```

**Production:**
```bash
# Add to crontab
crontab -e

# Add this line:
* * * * * cd /var/www/html/moneys && php artisan schedule:run >> /dev/null 2>&1
```

**Test schedule:**
```bash
php artisan schedule:list
```

---

### 10. ❌ Filament forms validation lỗi

**Nguyên nhân**: Validation rules hoặc database schema không khớp.

**Giải pháp**:

1. Check migration files
2. Run fresh migrate:
```bash
php artisan migrate:fresh --seed
```

3. Check form validation trong Resource files

---

## Debug Commands

### Check system status
```bash
# Environment info
php artisan about

# Database connection
php artisan db:show

# Queue status
php artisan queue:monitor

# Schedule list
php artisan schedule:list

# Route list
php artisan route:list
```

### Performance
```bash
# Cache everything for production
php artisan optimize

# Clear everything for development
php artisan optimize:clear
```

### Database
```bash
# Fresh start
php artisan migrate:fresh --seed

# Rollback & migrate
php artisan migrate:refresh

# Check migrations status
php artisan migrate:status
```

---

## Logs

### Check logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker.log

# Horizon logs (if using)
tail -f storage/logs/horizon.log
```

### Clear logs
```bash
# Clear all logs
> storage/logs/laravel.log
```

---

## Testing

### Run tests
```bash
# All tests
php artisan test

# Specific test
php artisan test --filter UserTest

# With coverage
php artisan test --coverage
```

---

## Emergency Reset

Nếu mọi thứ bị lỗi và bạn muốn reset toàn bộ:

```bash
# 1. Clear everything
php artisan optimize:clear

# 2. Fresh database
php artisan migrate:fresh --seed --seeder=AdminUserSeeder

# 3. Restart services
# Stop all running artisan commands
# Then start again:
php artisan serve

# In another terminal:
php artisan queue:work

# In another terminal:
php artisan schedule:work
```

---

## Getting Help

### Log Files
- **Laravel**: `storage/logs/laravel.log`
- **Worker**: `storage/logs/worker.log`

### Debug Mode
Enable in `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

⚠️ **Never enable debug mode in production!**

### Useful Commands
```bash
# Verbose output
php artisan <command> -v
php artisan <command> -vv
php artisan <command> -vvv

# Help for any command
php artisan help <command>
```

---

**Last Updated**: October 2025
