<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // delete category_templates table
        Schema::dropIfExists('project_category_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('project_category_templates', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }
};
