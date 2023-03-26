<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorProjectCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->renameProjectCategoriesTableToCategories();

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_project_category')->after('sort_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_project_category');
        });

        $this->renameCategoriesTableToProjectCategories();
    }

    private function renameProjectCategoriesTableToCategories()
    {
        DB::statement('RENAME TABLE project_categories TO categories');
    }

    private function renameCategoriesTableToProjectCategories()
    {
        DB::statement('RENAME TABLE categories TO project_categories');
    }
}
