<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('form_builder_forms');
            $table->text('name')->nullable();
            $table->integer('ordering')->default(1);
            $table->integer('columns')->default(1);
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('aside')->default(0);
            $table->boolean('compact')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_sections');
    }
};
