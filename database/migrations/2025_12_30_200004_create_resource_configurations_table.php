<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resource_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('resource_class')->unique()->comment('Full Filament Resource class name');
            $table->string('resource_name')->comment('Human-readable name');
            $table->string('navigation_group', 100)->nullable()->comment('Navigation group key');
            $table->string('icon', 100)->nullable()->comment('Heroicon name');
            $table->boolean('is_active')->default(true)->comment('Enable/disable globally');
            $table->integer('default_navigation_sort')->default(0)->comment('Default sort in navigation');
            $table->timestamps();

            $table->index('resource_class');
            $table->index('navigation_group');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_configurations');
    }
};
