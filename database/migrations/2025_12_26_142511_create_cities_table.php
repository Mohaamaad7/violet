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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('governorate_id')->constrained('governorates')->cascadeOnDelete();
            
            // Basic Information
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            
            // Optional Custom Shipping (overrides governorate default if set)
            $table->decimal('shipping_cost', 10, 2)->nullable()->comment('Custom shipping cost (overrides governorate)');
            
            // Status & Sorting
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['governorate_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
