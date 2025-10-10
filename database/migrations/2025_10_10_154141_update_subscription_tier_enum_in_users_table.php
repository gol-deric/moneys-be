<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, temporarily expand the enum to include 'pro'
        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_tier ENUM('free', 'premium', 'enterprise', 'pro') DEFAULT 'free'");

        // Update existing 'enterprise' tier to 'pro'
        DB::table('users')
            ->where('subscription_tier', 'enterprise')
            ->update(['subscription_tier' => 'pro']);

        // Update existing 'premium' tier to 'pro'
        DB::table('users')
            ->where('subscription_tier', 'premium')
            ->update(['subscription_tier' => 'pro']);

        // Now change enum to only have 'free' and 'pro'
        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_tier ENUM('free', 'pro') DEFAULT 'free'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old enum
        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_tier ENUM('free', 'premium', 'enterprise') DEFAULT 'free'");
    }
};
