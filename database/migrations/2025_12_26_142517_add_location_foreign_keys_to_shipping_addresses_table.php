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
        Schema::table('shipping_addresses', function (Blueprint $table) {
            // Add new location foreign keys (nullable for backward compatibility)
            $table->foreignId('country_id')->nullable()->after('customer_id')->constrained('countries')->nullOnDelete();
            $table->foreignId('governorate_id')->nullable()->after('country_id')->constrained('governorates')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('governorate_id')->constrained('cities')->nullOnDelete();
            
            // Add indexes for better performance
            $table->index(['country_id', 'governorate_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['country_id']);
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            
            // Drop columns
            $table->dropColumn(['country_id', 'governorate_id', 'city_id']);
        });
    }
};
