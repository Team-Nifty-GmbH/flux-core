<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_forms', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->nullableMorphs('model');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->json('options')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_forms');
    }
};
