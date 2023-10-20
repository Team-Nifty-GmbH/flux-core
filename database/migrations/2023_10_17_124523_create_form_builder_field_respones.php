<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_field_respones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('form_builder_forms');
            $table->foreignId('field_id')->constrained('form_builder_fields');
            $table->foreignId('response_id')->constrained('form_builder_responses');
            $table->longText('response');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_field_respones');
    }
};
