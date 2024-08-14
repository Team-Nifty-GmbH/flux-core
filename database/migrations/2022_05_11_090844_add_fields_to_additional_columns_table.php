<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->string('field_type')->default('text')->after('model_type');
            $table->json('label')->nullable()->after('field_type');
            $table->json('config')->nullable()->after('label');
            $table->json('validations')->nullable()->after('config');

            $table->unique(['name', 'model_type']);
        });
    }

    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropIndex('additional_columns_name_model_type_unique');
            $table->dropColumn(['field_type', 'label', 'config', 'validations']);
        });
    }
};
