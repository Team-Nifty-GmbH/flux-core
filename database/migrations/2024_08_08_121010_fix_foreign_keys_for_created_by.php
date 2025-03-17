<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign('project_tasks_created_by_foreign');
            $table->dropForeign('project_tasks_updated_by_foreign');
            $table->dropForeign('project_tasks_deleted_by_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreign('created_by', 'project_tasks_created_by_foreign')->on('users')->references('id');
            $table->foreign('updated_by', 'project_tasks_updated_by_foreign')->on('users')->references('id');
            $table->foreign('deleted_by', 'project_tasks_deleted_by_foreign')->on('users')->references('id');
        });
    }
};
