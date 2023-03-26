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
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropUnique('additional_columns_name_model_type_unique');

            $table->unique(['name', 'model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropUnique('additional_columns_name_model_type_model_id_unique');

            $table->unique(['name', 'model_type']);
        });
    }
};
