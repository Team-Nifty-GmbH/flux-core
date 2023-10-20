<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('form_builder_sections');
            $table->text('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->integer('ordering')->default(1);
            $table->text('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_fields');
    }
};
