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
        Schema::create('order_position_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_position_id')->references('id')->on('order_positions')->cascadeOnDelete();
            $table->foreignId('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->decimal('amount', 40, 10, true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_position_task');
    }
};
