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
        Schema::table('orders', function (Blueprint $table) {
            // For authenticated users - link to saved address
            $table->foreignId('shipping_address_id')->nullable()->after('discount_code_id')
                ->constrained('shipping_addresses')->nullOnDelete();
            
            // For guests - store address details inline
            $table->string('guest_name')->nullable()->after('shipping_address_id');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_governorate')->nullable();
            $table->string('guest_city')->nullable();
            $table->text('guest_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_address_id']);
            $table->dropColumn([
                'shipping_address_id',
                'guest_name',
                'guest_email',
                'guest_phone',
                'guest_governorate',
                'guest_city',
                'guest_address',
            ]);
        });
    }
};
