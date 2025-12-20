<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // سبب الحركة (للجرد)
            $table->string('reason_type', 50)->nullable()->after('notes');

            // الموظف المسؤول (عند employee_liability)
            $table->foreignId('responsible_id')
                ->nullable()
                ->after('reason_type')
                ->constrained('users')
                ->nullOnDelete();

            // سعر التكلفة لحظة الحركة (مهم للمحاسبة)
            $table->decimal('unit_cost', 10, 2)->nullable()->after('responsible_id');

            // Index for reporting
            $table->index('reason_type');
            $table->index('responsible_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
            $table->dropIndex(['reason_type']);
            $table->dropIndex(['responsible_id']);
            $table->dropColumn(['reason_type', 'responsible_id', 'unit_cost']);
        });
    }
};
