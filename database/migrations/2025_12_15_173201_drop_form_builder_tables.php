<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('form_builder_field_responses');
        Schema::dropIfExists('form_builder_responses');
        Schema::dropIfExists('form_builder_fields');
        Schema::dropIfExists('form_builder_sections');
        Schema::dropIfExists('form_builder_forms');
    }

    public function down(): void
    {
        Schema::create('form_builder_forms', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
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
        });

        Schema::create('form_builder_sections', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('form_id')->constrained('form_builder_forms')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('ordering')->default(0);
            $table->unsignedInteger('columns')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('form_builder_fields', function (Blueprint $table): void {
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

        Schema::create('form_builder_responses', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('form_id')->constrained('form_builder_forms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('form_builder_field_responses', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('form_id')->constrained('form_builder_forms')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_builder_fields')->cascadeOnDelete();
            $table->foreignId('response_id')->constrained('form_builder_responses')->cascadeOnDelete();
            $table->longText('response');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
