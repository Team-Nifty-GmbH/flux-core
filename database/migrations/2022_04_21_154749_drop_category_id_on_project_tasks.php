<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $this->migrateCategorizablesTable();

        Schema::table('project_tasks', function (Blueprint $table): void {
            $table->dropForeign('project_tasks_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table): void {
            $table->unsignedBigInteger('category_id')->after('project_id');
        });

        $this->rollbackCategorizablesTable();

        Schema::table('project_tasks', function (Blueprint $table): void {
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    private function migrateCategorizablesTable(): void
    {
        DB::statement('INSERT INTO categorizables(category_id, categorizable_type, categorizable_id)
            SELECT category_id, \'' . trim(
            json_encode('FluxErp\\Models\\ProjectTask', JSON_UNESCAPED_SLASHES), '"'
        ) . '\', id
            FROM project_tasks'
        );
    }

    private function rollbackCategorizablesTable(): void
    {
        DB::statement('UPDATE project_tasks
            INNER JOIN categorizables
            ON project_tasks.id = categorizables.categorizable_id
            AND categorizables.categorizable_type = \'' . trim(
            json_encode('FluxErp\\Models\\ProjectTask', JSON_UNESCAPED_SLASHES), '"'
        ) . '\'
            SET project_tasks.category_id = categorizables.category_id'
        );
    }
};
