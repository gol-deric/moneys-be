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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('fcm_token')->unique();
            $table->string('device_type')->nullable(); // android, ios, web
            $table->string('device_name')->nullable(); // e.g., "Samsung Galaxy S21"
            $table->string('app_version')->nullable(); // e.g., "1.0.0"

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            // Index for faster queries
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
