<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // خطوة التنظيف: حذف بيانات المرتجعات القديمة
        // (آمن لأن User قال يمكن فقدان الطلبات والعملاء)
        // ============================================
        DB::table('return_items')->truncate();
        DB::table('returns')->truncate();

        // ============================================
        // تحويل Orders status
        // ============================================

        // تحويل البيانات النصية إلى أرقام
        DB::statement("
            UPDATE orders 
            SET status = CASE 
                WHEN status = 'pending' THEN 0
                WHEN status = 'processing' THEN 1
                WHEN status = 'shipped' THEN 2
                WHEN status = 'delivered' THEN 3
                WHEN status = 'cancelled' THEN 4
                WHEN status = 'rejected' THEN 5
                ELSE CAST(status AS UNSIGNED)
            END
            WHERE status NOT REGEXP '^[0-5]$'
        ");

        // Fix any invalid values
        DB::statement("UPDATE orders SET status = 0 WHERE status > 5 OR status < 0 OR status IS NULL");

        // تغيير نوع العمود
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
        });

        // ============================================
        // تحويل Returns columns (الجدول فارغ الآن)
        // ============================================
        Schema::table('returns', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
            $table->tinyInteger('type')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // تحويل Orders status للنصوص
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        DB::statement("
            UPDATE orders SET status = CASE 
                WHEN status = 0 OR status = '0' THEN 'pending'
                WHEN status = 1 OR status = '1' THEN 'processing'
                WHEN status = 2 OR status = '2' THEN 'shipped'
                WHEN status = 3 OR status = '3' THEN 'delivered'
                WHEN status = 4 OR status = '4' THEN 'cancelled'
                WHEN status = 5 OR status = '5' THEN 'rejected'
                ELSE 'pending'
            END
        ");

        // تحويل Returns columns للنصوص
        Schema::table('returns', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->string('type')->default('rejection')->change();
        });
    }
};
