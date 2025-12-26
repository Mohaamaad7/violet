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
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            
            // Basic Information
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            
            // Shipping Settings
            $table->decimal('shipping_cost', 10, 2)->default(0)->comment('Default shipping cost for this governorate');
            $table->integer('delivery_days')->default(3)->comment('Estimated delivery days');
            
            // Status & Sorting
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['country_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
