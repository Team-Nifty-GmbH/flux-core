<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingForeignKeysOnProjectCategoryTemplatesTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_category_templates', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('project_category_templates', function (Blueprint $table): void {
            $table->dropForeign('project_category_templates_created_by_foreign');
            $table->dropForeign('project_category_templates_updated_by_foreign');
        });
    }
}
