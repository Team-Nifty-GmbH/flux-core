<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_position_task')) {
            return;
        }

        Schema::create('order_position_task', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_position_id')->index('order_position_task_order_position_id_foreign');
            $table->unsignedBigInteger('task_id')->index('order_position_task_task_id_foreign');
            $table->decimal('amount', 40, 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_position_task');
    }
};
