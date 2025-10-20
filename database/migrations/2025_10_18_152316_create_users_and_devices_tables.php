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
        Schema::dropIfExists('sessions');

        // Drop and recreate users table with UUID
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('full_name')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('language')->default('en');
            $table->string('currency')->default('USD');
            $table->timestamp('last_logged_in')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        // Recreate sessions table with UUID foreign key
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
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
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
