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
            $table->enum('return_status', ['none', 'requested', 'approved', 'completed'])->default('none')->after('status');
            $table->timestamp('rejected_at')->nullable()->after('cancelled_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            
            $table->index('return_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['return_status']);
            $table->dropColumn(['return_status', 'rejected_at', 'rejection_reason']);
        });
    }
};
