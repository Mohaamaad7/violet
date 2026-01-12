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
        Schema::create('campaign_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate offer in same campaign
            $table->unique(['campaign_id', 'offer_id'], 'unique_campaign_offer');
            
            // Indexes
            $table->index('campaign_id');
            $table->index('offer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_offers');
    }
};
