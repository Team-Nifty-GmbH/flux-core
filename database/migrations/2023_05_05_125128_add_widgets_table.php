<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->morphs('widgetable');
            $table->string('component_name');
            $table->string('name')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('height')->default(1);
            $table->unsignedInteger('width')->default(1);
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
