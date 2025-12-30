<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('widget_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('widget_class')->unique()->comment('Full class name');
            $table->string('widget_name')->comment('Human-readable name');
            $table->string('widget_group', 100)->nullable()->comment('Group: sales, inventory, general');
            $table->text('description')->nullable()->comment('Widget description');
            $table->boolean('is_active')->default(true)->comment('Enable/disable globally');
            $table->integer('default_order')->default(0)->comment('Default position');
            $table->integer('default_column_span')->default(1)->comment('Default width 1-4');
            $table->timestamps();

            $table->index('widget_class');
            $table->index('is_active');
            $table->index('widget_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_configurations');
    }
};
