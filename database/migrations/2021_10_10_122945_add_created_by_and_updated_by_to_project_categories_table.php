<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByAndUpdatedByToProjectCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->after('created_at')->nullable();
            $table->unsignedBigInteger('updated_by')->after('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_categories', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
}
