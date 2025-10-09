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
        Schema::table('users', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['id', 'name']);

            // Add UUID primary key
            $table->uuid('id')->primary()->first();

            $table->string('full_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->string('fcm_token')->nullable();

            $table->string('locale', 10)->default('en');
            $table->string('currency_code', 3)->default('USD');
            $table->enum('theme', ['light', 'dark'])->default('light');

            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('email_notifications')->default(true);

            $table->enum('subscription_tier', ['free', 'premium', 'enterprise'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'full_name',
                'avatar_url',
                'is_guest',
                'fcm_token',
                'locale',
                'currency_code',
                'theme',
                'notifications_enabled',
                'email_notifications',
                'subscription_tier',
                'subscription_expires_at',
            ]);

            // Restore original columns
            $table->dropColumn('id');
            $table->id()->first();
            $table->string('name');
        });
    }
};
