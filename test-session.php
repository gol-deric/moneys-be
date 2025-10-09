<?php
// Quick session test script
// Run: php test-session.php

echo "🔍 Testing Session Configuration...\n\n";

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test 1: Check .env
echo "1️⃣ Checking .env configuration:\n";
echo "   SESSION_DRIVER: " . env('SESSION_DRIVER') . "\n";
echo "   SESSION_LIFETIME: " . env('SESSION_LIFETIME') . " minutes\n";
echo "   APP_URL: " . env('APP_URL') . "\n";
echo "   SESSION_DOMAIN: " . env('SESSION_DOMAIN', 'null') . "\n\n";

// Test 2: Check config
echo "2️⃣ Checking config/session.php:\n";
echo "   Driver: " . config('session.driver') . "\n";
echo "   Lifetime: " . config('session.lifetime') . " minutes\n";
echo "   Cookie: " . config('session.cookie') . "\n";
echo "   Path: " . config('session.path') . "\n";
echo "   Domain: " . (config('session.domain') ?? 'null') . "\n";
echo "   Secure: " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "   SameSite: " . config('session.same_site') . "\n\n";

// Test 3: Check storage
echo "3️⃣ Checking session storage:\n";
$driver = config('session.driver');
if ($driver === 'file') {
    $path = storage_path('framework/sessions');
    echo "   Storage path: $path\n";
    echo "   Path exists: " . (is_dir($path) ? '✅ Yes' : '❌ No') . "\n";
    echo "   Writable: " . (is_writable($path) ? '✅ Yes' : '❌ No') . "\n";
    $files = glob($path . '/*');
    echo "   Session files: " . count($files) . "\n";
} else if ($driver === 'database') {
    try {
        $count = DB::table('sessions')->count();
        echo "   Sessions in DB: $count\n";
        echo "   Database: ✅ Connected\n";
    } catch (Exception $e) {
        echo "   Database: ❌ Error - " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Test 4: Check CSRF
echo "4️⃣ Checking CSRF configuration:\n";
echo "   CSRF middleware: " . (class_exists('Illuminate\Foundation\Http\Middleware\VerifyCsrfToken') ? '✅ Loaded' : '❌ Missing') . "\n\n";

// Test 5: Recommendations
echo "💡 Recommendations:\n";
if (env('SESSION_LIFETIME') < 720) {
    echo "   ⚠️  SESSION_LIFETIME is low. Increase to 720 for better UX\n";
} else {
    echo "   ✅ SESSION_LIFETIME is good\n";
}

if (env('SESSION_DRIVER') === 'database') {
    echo "   💡 Consider using SESSION_DRIVER=file for development\n";
} else {
    echo "   ✅ Using file driver (good for development)\n";
}

if (env('APP_ENV') === 'production' && env('APP_DEBUG') === 'true') {
    echo "   ⚠️  APP_DEBUG is true in production!\n";
}

echo "\n✅ Test completed!\n";
