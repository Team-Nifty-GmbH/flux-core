<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCategoryIdToParentIdOnCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('project_categories_category_id_foreign');
            $table->dropForeign('project_categories_created_by_foreign');
            $table->dropForeign('project_categories_updated_by_foreign');
            $table->dropIndex('project_categories_category_id_foreign');
            $table->dropIndex('project_categories_created_by_foreign');
            $table->dropIndex('project_categories_updated_by_foreign');

            $table->renameColumn('category_id', 'parent_id');

            $table->foreign('parent_id')->references('id')->on('categories');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('categories_parent_id_foreign');
            $table->dropIndex('categories_parent_id_foreign');

            $table->renameColumn('parent_id', 'category_id');

            $table->foreign('category_id')->references('id')->on('categories');

            $table->dropForeign('categories_created_by_foreign');
            $table->dropForeign('categories_updated_by_foreign');
            $table->dropIndex('categories_created_by_foreign');
            $table->dropIndex('categories_updated_by_foreign');

            $table->foreign('created_by', 'project_categories_created_by_foreign')
                ->references('id')
                ->on('users');
            $table->foreign('updated_by', 'project_categories_updated_by_foreign')
                ->references('id')
                ->on('users');
        });
    }
}
