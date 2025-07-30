<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_position_task', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_position_id')
                ->constrained('order_positions')
                ->cascadeOnDelete();
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();
            $table->decimal('amount', 40, 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_position_task');
    }
};
