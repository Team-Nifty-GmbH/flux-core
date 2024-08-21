<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameProjectIdToParentIdOnProjectsTable extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_project_id_foreign');
            $table->dropIndex('projects_project_id_foreign');

            $table->renameColumn('project_id', 'parent_id');

            $table->foreign('parent_id')->references('id')->on('projects');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_parent_id_foreign');
            $table->dropIndex('projects_parent_id_foreign');

            $table->renameColumn('parent_id', 'project_id');

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }
}
