<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('meta');

        Schema::dropIfExists('additional_columns');
    }

    public function down(): void
    {
        Schema::create('additional_columns', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('model_type');
            $table->string('model_id')->nullable();
            $table->string('field_type')->default('text');
            $table->string('label')->nullable();
            $table->json('config')->nullable();
            $table->json('validations')->nullable();
            $table->json('values')->nullable();
            $table->boolean('is_translatable')->default(false);
            $table->boolean('is_customer_editable')->default(false);
            $table->boolean('is_frontend_visible')->default(false);
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->unique(['name', 'model_type', 'model_id']);
        });

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
};
