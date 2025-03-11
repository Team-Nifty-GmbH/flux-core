<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectCategoryTemplateTable extends Migration
{
    public function up(): void
    {
        Schema::create('project_category_template', function (Blueprint $table): void {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('template_id');

            $table->foreign('category_id')
                ->references('id')
                ->on('project_categories');

            $table->foreign('template_id')
                ->references('id')
                ->on('project_category_templates')
                ->onDelete('cascade');
            $table->primary(['category_id', 'template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_category_template');
    }
}
