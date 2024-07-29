<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->string('field_type')->default('text')->after('model_type');
            $table->json('label')->nullable()->after('field_type');
            $table->json('config')->nullable()->after('label');
            $table->json('validations')->nullable()->after('config');

            $table->unique(['name', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropIndex('additional_columns_name_model_type_unique');
            $table->dropColumn(['field_type', 'label', 'config', 'validations']);
        });
    }
};
