#!/bin/bash

# MoneyS - Fix Session/CSRF Token Expired Error
# Quick script to fix "419 Page Expired" errors

echo "🔧 MoneyS - Fixing Session Errors..."
echo ""

# Step 1: Clear all caches
echo "1️⃣ Clearing all Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Step 2: Clear old sessions from database
echo "2️⃣ Clearing old sessions from database..."
mysql -u thuongnm -p'Thuong99@' moneys -e "DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY));"
echo "✅ Old sessions cleared"
echo ""

# Step 3: Verify .env settings
echo "3️⃣ Checking SESSION_LIFETIME in .env..."
if grep -q "SESSION_LIFETIME=120" .env; then
    echo "⚠️  SESSION_LIFETIME is 120 minutes (2 hours)"
    echo "   Consider increasing to 720 (12 hours) for better UX"
    echo ""
    echo "   Update .env manually:"
    echo "   SESSION_LIFETIME=720"
else
    echo "✅ SESSION_LIFETIME is already configured"
fi
echo ""

# Step 4: Restart instructions
echo "4️⃣ Next steps:"
echo "   - Close your browser tab completely"
echo "   - Restart Laravel server (Ctrl+C, then 'php artisan serve')"
echo "   - Open http://localhost:8000/admin in new tab"
echo "   - Login again with: admin@moneys.com / password"
echo ""

echo "🎉 Done! Session errors should be fixed."
echo ""
echo "💡 Tips to prevent this error:"
echo "   • Don't leave admin panel idle for too long"
echo "   • Increase SESSION_LIFETIME in .env"
echo "   • Consider using SESSION_DRIVER=file for development"
