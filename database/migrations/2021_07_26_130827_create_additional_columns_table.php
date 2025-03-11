<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalColumnsTable extends Migration
{
    public function up(): void
    {
        Schema::create('additional_columns', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('model');
            $table->json('values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_columns');
    }
}
