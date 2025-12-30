<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_widget_defaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('widget_configuration_id')->constrained('widget_configurations')->cascadeOnDelete();
            $table->boolean('is_visible')->default(true)->comment('Role default visibility');
            $table->integer('order_position')->default(0)->comment('Role default position');
            $table->integer('column_span')->default(1)->comment('Role default width 1-4');
            $table->timestamps();

            $table->unique(['role_id', 'widget_configuration_id'], 'unique_role_widget');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_widget_defaults');
    }
};
