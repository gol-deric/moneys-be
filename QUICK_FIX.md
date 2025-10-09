# MoneyS - Quick Fix Commands

## 🔥 Lỗi "419 Page Expired" (CSRF Token)

### Cách 1: Script tự động (Nhanh nhất!)
```bash
./fix-session-error.sh
```

### Cách 2: Manual commands
```bash
# Clear all caches
php artisan optimize:clear

# Clear config
php artisan config:clear

# Restart server
php artisan serve
```

### Cách 3: Browser fix
1. ✅ Close tất cả tabs admin panel
2. ✅ Clear browser cache (Ctrl+Shift+Delete)
3. ✅ Mở tab mới: http://localhost:8000/admin
4. ✅ Login lại

---

## 🔧 Các lỗi khác

### Không login được admin
```bash
php artisan db:seed --class=AdminUserSeeder
# Email: admin@moneys.com
# Password: password
```

### Database connection error
```bash
php artisan migrate:fresh --seed
```

### Routes không hoạt động
```bash
php artisan route:clear
php artisan route:list
```

### Cache issues
```bash
php artisan optimize:clear
```

---

## 🚀 Start server lại

```bash
# Stop server: Ctrl + C

# Clear everything
php artisan optimize:clear

# Start fresh
php artisan serve

# In another terminal (queue worker)
php artisan queue:work

# In another terminal (scheduler)
php artisan schedule:work
```

---

## 📊 Check status

```bash
# System info
php artisan about

# Routes
php artisan route:list --path=admin

# Database
php artisan db:show

# Queue
php artisan queue:monitor
```

---

## 💡 Prevention Tips

### Tăng session lifetime
Trong `.env`:
```env
SESSION_LIFETIME=720  # 12 hours
```

### Đổi session driver
Trong `.env`:
```env
SESSION_DRIVER=file  # Thay vì database
```

Sau đó:
```bash
php artisan config:clear
```

---

## 🆘 Emergency Reset

Nếu **MỌI THỨ** bị lỗi:

```bash
# 1. Stop all servers (Ctrl+C)

# 2. Clear everything
php artisan optimize:clear

# 3. Fresh database
php artisan migrate:fresh --seed --seeder=AdminUserSeeder

# 4. Clear browser
# Close all tabs, clear cache

# 5. Restart
php artisan serve

# 6. Login
# http://localhost:8000/admin
# admin@moneys.com / password
```

---

## 📞 Get Help

Xem chi tiết tại:
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Full troubleshooting guide
- **[ADMIN_GUIDE.md](ADMIN_GUIDE.md)** - Admin panel guide
- **[README.md](README.md)** - Main documentation

---

**Quick Links:**
- Admin Panel: http://localhost:8000/admin
- API Docs: http://localhost:8000/api/v1
- Horizon (Queue): http://localhost:8000/horizon
