<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('print_data');
    }

    public function down(): void
    {
        Schema::create('print_data', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->nullableMorphs('model');
            $table->json('data')->nullable();
            $table->string('view');
            $table->string('template_name')->nullable();
            $table->integer('sort')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });
    }
};
