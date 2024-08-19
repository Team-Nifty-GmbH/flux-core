<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByAndUpdatedByToProjectCategoryTemplatesTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_category_templates', function (Blueprint $table) {
            $table->char('uuid', 36)->after('id');
            $table->unsignedBigInteger('created_by')->after('created_at')->nullable();
            $table->unsignedBigInteger('updated_by')->after('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_category_templates', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'created_by', 'updated_by']);
        });
    }
}
