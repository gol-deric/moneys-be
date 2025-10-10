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
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Google Play Billing info
            $table->string('product_id'); // e.g., "moneys_pro_yearly"
            $table->string('order_id')->unique(); // Google Play order ID
            $table->string('purchase_token')->unique(); // Google Play purchase token
            $table->text('receipt_data')->nullable(); // Full receipt JSON from Google Play

            // Purchase details
            $table->enum('platform', ['google_play', 'app_store', 'web'])->default('google_play');
            $table->enum('purchase_type', ['subscription', 'one_time'])->default('subscription');
            $table->decimal('amount', 10, 2); // Price paid
            $table->string('currency', 3)->default('USD');

            // Subscription details
            $table->timestamp('purchased_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_renewing')->default(true);

            // Status
            $table->enum('status', ['pending', 'verified', 'expired', 'cancelled', 'refunded'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
