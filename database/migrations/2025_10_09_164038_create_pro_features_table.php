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
        Schema::create('pro_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Custom Notifications", "Export PDF", "Reports"
            $table->string('key')->unique(); // e.g., "custom_notifications", "export_pdf"
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->decimal('price', 10, 2)->default(0); // Additional price if feature is separate
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_features');
    }
};
