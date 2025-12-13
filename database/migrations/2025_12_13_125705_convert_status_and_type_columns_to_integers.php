<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // تحويل Orders status
        DB::statement("
            UPDATE orders SET status = CASE 
                WHEN status = 'pending' THEN 0
                WHEN status = 'processing' THEN 1
                WHEN status = 'shipped' THEN 2
                WHEN status = 'delivered' THEN 3
                WHEN status = 'cancelled' THEN 4
                WHEN status = 'rejected' THEN 5
                ELSE 0
            END
        ");
        
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
        });

        // تحويل Returns status
        DB::statement("
            UPDATE returns SET status = CASE 
                WHEN status = 'pending' THEN 0
                WHEN status = 'approved' THEN 1
                WHEN status = 'rejected' THEN 2
                WHEN status = 'completed' THEN 3
                ELSE 0
            END
        ");
        
        Schema::table('returns', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
        });

        // تحويل Returns type
        DB::statement("
            UPDATE returns SET type = CASE 
                WHEN type = 'rejection' THEN 0
                WHEN type = 'return_after_delivery' THEN 1
                ELSE 0
            END
        ");
        
        Schema::table('returns', function (Blueprint $table) {
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
                WHEN status = 0 THEN 'pending'
                WHEN status = 1 THEN 'processing'
                WHEN status = 2 THEN 'shipped'
                WHEN status = 3 THEN 'delivered'
                WHEN status = 4 THEN 'cancelled'
                WHEN status = 5 THEN 'rejected'
                ELSE 'pending'
            END
        ");

        // تحويل Returns status للنصوص
        Schema::table('returns', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
        
        DB::statement("
            UPDATE returns SET status = CASE 
                WHEN status = 0 THEN 'pending'
                WHEN status = 1 THEN 'approved'
                WHEN status = 2 THEN 'rejected'
                WHEN status = 3 THEN 'completed'
                ELSE 'pending'
            END
        ");

        // تحويل Returns type للنصوص
        Schema::table('returns', function (Blueprint $table) {
            $table->string('type')->default('rejection')->change();
        });
        
        DB::statement("
            UPDATE returns SET type = CASE 
                WHEN type = 0 THEN 'rejection'
                WHEN type = 1 THEN 'return_after_delivery'
                ELSE 'rejection'
            END
        ");
    }
};
