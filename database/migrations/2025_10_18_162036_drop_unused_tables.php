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
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Drop all unused tables
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('pro_features');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('device_tokens');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't recreate these tables in down()
        // as they are being permanently removed
    }
};
