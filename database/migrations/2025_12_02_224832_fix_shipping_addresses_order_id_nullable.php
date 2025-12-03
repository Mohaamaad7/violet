<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fixes:
     * 1. Make email column nullable (for saved addresses without email)
     * 2. Drop unique constraint on order_id (since it's nullable for saved addresses)
     */
    public function up(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            // Make email nullable - saved addresses may not have email
            $table->string('email')->nullable()->change();
            
            // Drop unique constraint on order_id if it exists
            // order_id is already nullable, but unique constraint may cause issues
            $table->dropUnique(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('order_id');
        });
    }
};
