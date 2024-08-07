<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('additional_column_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('model');
            $table->string('key')->nullable();
            $table->longText('value')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        DB::statement(
            'INSERT INTO meta '
            . '(additional_column_id, model_type, model_id, `key`, value, created_at, updated_at)'
            . '(SELECT additional_column_id, ac.model_type, model_has_values.model_id, ac.name, model_has_values.value,'
            . 'now(), now() '
            . 'FROM model_has_values JOIN additional_columns ac ON ac.id = model_has_values.additional_column_id)'
        );

        Schema::dropIfExists('model_has_values');
    }

    public function down(): void
    {
        Schema::create('model_has_values', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('additional_column_id');
            $table->string('value')->nullable();

            $table->primary(['model_id', 'additional_column_id']);

            $table->foreign('additional_column_id')->references('id')->on('additional_columns');
        });

        DB::statement(
            'INSERT INTO model_has_values '
            . '(model_id, additional_column_id, value)'
            . '(SELECT model_id, additional_column_id, value FROM meta WHERE additional_column_id IS NOT NULL)'
        );

        Schema::dropIfExists('metadata');
    }
};
