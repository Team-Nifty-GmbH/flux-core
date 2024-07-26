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
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');

            $table->dropIndex('additional_columns_model_index');
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropIndex('additional_columns_model_type_model_id_index');
            $table->dropColumn('model_id');

            $table->index('model_type', 'additional_columns_model_index');
        });
    }
};
