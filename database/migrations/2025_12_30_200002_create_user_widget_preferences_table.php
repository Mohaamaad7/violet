<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_widget_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('widget_configuration_id')->constrained('widget_configurations')->cascadeOnDelete();
            $table->boolean('is_visible')->default(true)->comment('User visibility override');
            $table->integer('order_position')->default(0)->comment('User-defined position');
            $table->integer('column_span')->default(1)->comment('User-defined width 1-4');
            $table->timestamps();

            $table->unique(['user_id', 'widget_configuration_id'], 'unique_user_widget');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_widget_preferences');
    }
};
