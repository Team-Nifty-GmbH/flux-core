<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_position_task', function (Blueprint $table) {
            $table->foreign(['order_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['task_id'])->references(['id'])->on('tasks')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_position_task', function (Blueprint $table) {
            $table->dropForeign('order_position_task_order_position_id_foreign');
            $table->dropForeign('order_position_task_task_id_foreign');
        });
    }
};
