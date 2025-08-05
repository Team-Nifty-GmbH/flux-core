<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderPositionIdToProjectTasksTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_position_id')->after('user_id')->nullable();

            $table->foreign('order_position_id')->references('id')->on('order_positions');
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table): void {
            $table->dropForeign('project_tasks_order_position_id_foreign');
            $table->dropColumn('order_position_id');
        });
    }
}
