<?php

use FluxErp\Models\ProjectCategoryTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->migrateCategorizablesTable();

        Schema::table('project_category_template', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('project_category_template', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('template_id');

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('template_id')->references('id')->on('project_category_templates');
            $table->primary(['category_id', 'template_id']);
        });

        $this->rollbackCategorizablesTable();
    }

    private function migrateCategorizablesTable()
    {
        DB::statement('INSERT INTO categorizables(category_id, categorizable_type, categorizable_id)
            SELECT category_id, \'' . trim(
            json_encode(ProjectCategoryTemplate::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\', template_id
            FROM project_category_template'
        );
    }

    private function rollbackCategorizablesTable()
    {
        DB::statement('INSERT INTO project_category_template(category_id, template_id)
            SELECT category_id, categorizable_id
            FROM categorizables WHERE categorizable_type = \'' . trim(
            json_encode(ProjectCategoryTemplate::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\''
        );
    }
};
