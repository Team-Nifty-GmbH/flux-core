<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('widgets')) {
            Schema::create('widgets', function (Blueprint $table): void {
                $table->id();
                $table->char('uuid', 36);
                $table->morphs('widgetable');
                $table->string('component_name');
                $table->string('dashboard_component');
                $table->string('group')->nullable();
                $table->string('name')->nullable();
                $table->json('config')->nullable();
                $table->unsignedInteger('height')->default(1);
                $table->unsignedInteger('width')->default(1);
                $table->unsignedInteger('order_column')->default(0);
                $table->unsignedInteger('order_row')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
