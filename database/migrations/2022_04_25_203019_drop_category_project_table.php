<?php

use FluxErp\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $this->migrateCategorizablesTable();

        Schema::table('category_project', function (Blueprint $table): void {
            $table->dropIfExists();
        });
    }

    public function down(): void
    {
        Schema::create('category_project', function (Blueprint $table): void {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('project_id');

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('project_id')->references('id')->on('projects');
            $table->primary(['category_id', 'project_id']);
        });

        $this->rollbackCategorizablesTable();
    }

    private function migrateCategorizablesTable(): void
    {
        DB::statement('INSERT INTO categorizables(category_id, categorizable_type, categorizable_id)
            SELECT category_id, \'' . trim(
            json_encode(Project::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\', project_id
            FROM category_project'
        );
    }

    private function rollbackCategorizablesTable(): void
    {
        DB::statement('INSERT INTO category_project(category_id, project_id)
            SELECT category_id, categorizable_id
            FROM categorizables WHERE categorizable_type = \'' . trim(
            json_encode(Project::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\''
        );
    }
};
