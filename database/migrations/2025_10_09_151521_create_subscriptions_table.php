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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('name');
            $table->string('icon_url')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency_code', 3)->default('USD');

            $table->date('start_date');
            $table->integer('billing_cycle_count')->default(1);
            $table->enum('billing_cycle_period', ['day', 'month', 'quarter', 'year'])->default('month');

            $table->string('category')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
