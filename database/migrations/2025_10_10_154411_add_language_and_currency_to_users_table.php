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
            // Rename locale to language for clarity
            $table->renameColumn('locale', 'language');

            // Rename currency_code to currency for consistency
            $table->renameColumn('currency_code', 'currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('language', 'locale');
            $table->renameColumn('currency', 'currency_code');
        });
    }
};
