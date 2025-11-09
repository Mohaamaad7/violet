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
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->integer('instagram_followers')->default(0);
            $table->integer('facebook_followers')->default(0);
            $table->integer('tiktok_followers')->default(0);
            $table->integer('youtube_followers')->default(0);
            $table->integer('twitter_followers')->default(0);
            $table->json('content_type')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->decimal('total_commission_earned', 10, 2)->default(0);
            $table->decimal('total_commission_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
