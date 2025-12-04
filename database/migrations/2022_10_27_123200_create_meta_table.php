<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('meta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('additional_column_id')
                ->nullable()
                ->constrained('additional_columns')
                ->cascadeOnDelete();
            $table->morphs('model');
            $table->string('key')->nullable();
            $table->longText('value')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta');
    }
};
