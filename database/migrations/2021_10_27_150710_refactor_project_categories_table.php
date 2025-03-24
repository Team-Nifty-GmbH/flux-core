<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorProjectCategoriesTable extends Migration
{
    public function up(): void
    {
        $this->renameProjectCategoriesTableToCategories();

        Schema::table('categories', function (Blueprint $table): void {
            $table->boolean('is_project_category')->after('sort_number');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('is_project_category');
        });

        $this->renameCategoriesTableToProjectCategories();
    }

    private function renameCategoriesTableToProjectCategories(): void
    {
        DB::statement('RENAME TABLE categories TO project_categories');
    }

    private function renameProjectCategoriesTableToCategories(): void
    {
        DB::statement('RENAME TABLE project_categories TO categories');
    }
}
