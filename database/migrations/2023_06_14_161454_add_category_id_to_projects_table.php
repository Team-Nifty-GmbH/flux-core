<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')
                ->after('uuid')
                ->nullable();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories');
            $table->dropForeign('projects_project_category_template_id_foreign');
            $table->dropColumn('project_category_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('project_category_template_id')
                ->nullable()
                ->after('uuid');
            $table->foreign('project_category_template_id')
                ->references('id')
                ->on('project_category_templates');
            $table->dropForeign('projects_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }
};
