<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasValuesTable extends Migration
{
    public function up(): void
    {
        Schema::create('model_has_values', function (Blueprint $table): void {
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('additional_column_id');
            $table->string('value')->nullable();

            $table->primary(['model_id', 'additional_column_id']);

            $table->foreign('additional_column_id')->references('id')->on('additional_columns');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_has_values');
    }
}
