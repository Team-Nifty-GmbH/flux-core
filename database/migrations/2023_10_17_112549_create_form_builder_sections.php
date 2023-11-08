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
            $table->uuid();
            $table->foreignId('form_id')->constrained('form_builder_forms');
            $table->string('name')->nullable();
            $table->unsignedInteger('ordering')->default(0);
            $table->unsignedInteger('columns')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_sections');
    }
};
