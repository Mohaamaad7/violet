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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->string('code', 2)->unique()->comment('ISO 3166-1 alpha-2 code');
            
            // Contact & Currency
            $table->string('phone_code', 10)->comment('e.g., +20');
            $table->string('currency_code', 3)->default('EGP')->comment('ISO 4217 code');
            
            // Status & Sorting
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
