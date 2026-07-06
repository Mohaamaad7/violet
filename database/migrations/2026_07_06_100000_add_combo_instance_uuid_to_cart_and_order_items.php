<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add combo_instance_uuid and original_price to cart_items and order_items.
     *
     * combo_instance_uuid: Groups cart/order items that belong to a single combo bundle.
     * original_price: Preserves the pre-discount price for UI anchoring and ERP reporting.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('combo_instance_uuid', 36)->nullable()->after('price');
            $table->decimal('original_price', 10, 2)->nullable()->after('combo_instance_uuid');
            $table->index('combo_instance_uuid', 'cart_items_combo_uuid_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('combo_instance_uuid', 36)->nullable()->after('subtotal');
            $table->decimal('original_price', 10, 2)->nullable()->after('combo_instance_uuid');
            $table->index('combo_instance_uuid', 'order_items_combo_uuid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_combo_uuid_idx');
            $table->dropColumn(['combo_instance_uuid', 'original_price']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_combo_uuid_idx');
            $table->dropColumn(['combo_instance_uuid', 'original_price']);
        });
    }
};
