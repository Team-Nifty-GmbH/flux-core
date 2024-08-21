<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToModelOnAdditionalColumnsTable extends Migration
{
    public function up(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->string('model')->index()->change();
        });
    }

    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropIndex('additional_columns_model_index');
        });
    }
}
