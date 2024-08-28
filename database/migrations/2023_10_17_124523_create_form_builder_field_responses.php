<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_field_responses', function (Blueprint $table) {
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

    public function down(): void
    {
        Schema::dropIfExists('form_builder_field_responses');
    }
};
