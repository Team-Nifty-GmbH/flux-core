<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('additional_columns', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('field_type')->default('text');
            $table->string('label')->nullable();
            $table->json('config')->nullable();
            $table->json('validations')->nullable();
            $table->json('values')->nullable();
            $table->boolean('is_customer_editable')
                ->default(false)
                ->comment('If set to true the customer can edit this field in the customer portal.');
            $table->boolean('is_frontend_visible')->default(true);
            $table->boolean('is_translatable')->default(false);
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->unique(['name', 'model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_columns');
    }
};
