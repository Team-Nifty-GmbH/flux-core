<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_fields', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('section_id')->constrained('form_builder_sections')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->unsignedInteger('ordering')->default(0);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_fields');
    }
};
