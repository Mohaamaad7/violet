<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('navigation_group_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('group_key', 100)->unique()->comment('Unique key e.g. catalog, sales');
            $table->string('group_label_ar')->comment('Arabic label');
            $table->string('group_label_en')->comment('English label');
            $table->string('icon', 100)->nullable()->comment('Heroicon name');
            $table->boolean('is_active')->default(true)->comment('Enable/disable globally');
            $table->integer('default_order')->default(0)->comment('Default sort order');
            $table->timestamps();

            $table->index('group_key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_group_configurations');
    }
};
