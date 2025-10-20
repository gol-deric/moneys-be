<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old tables if exist
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('user_devices');

        // Recreate users table with new schema
        Schema::table('users', function (Blueprint $table) {
            // Drop columns we don't need anymore
            if (Schema::hasColumn('users', 'avatar_url')) {
                $table->dropColumn([
                    'avatar_url',
                    'is_admin',
                    'fcm_token',
                    'theme',
                    'notifications_enabled',
                    'email_notifications',
                    'subscription_tier',
                    'subscription_expires_at',
                    'device_id',
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Add new columns according to schema
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'is_guest')) {
                $table->boolean('is_guest')->default(false)->after('full_name');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_guest');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->default('en')->after('is_active');
            }
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('USD')->after('language');
            }
            if (!Schema::hasColumn('users', 'last_logged_in')) {
                $table->timestamp('last_logged_in')->nullable()->after('currency');
            }

            // Make email nullable for guest accounts
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });

        // Create user_devices table
        Schema::create('user_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('device_id');
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable(); // android, ios, web
            $table->text('fcm_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'is_guest',
                'is_active',
                'language',
                'currency',
                'last_logged_in',
            ]);
        });
    }
};
