<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('widgets')) {
            return;
        }

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->string('widgetable_type');
            $table->unsignedBigInteger('widgetable_id');
            $table->string('component_name');
            $table->string('name')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('height')->default(1);
            $table->unsignedInteger('width')->default(1);
            $table->unsignedInteger('order_column')->default(0);
            $table->unsignedInteger('order_row')->default(0);
            $table->timestamps();

            $table->index(['widgetable_type', 'widgetable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
