<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('project_categories', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('sort_number');
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('project_categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_categories');
    }
}
