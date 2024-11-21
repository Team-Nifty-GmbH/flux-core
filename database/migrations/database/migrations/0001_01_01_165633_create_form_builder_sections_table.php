<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('form_builder_sections')) {
            return;
        }

        Schema::create('form_builder_sections', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('form_id')->index('form_builder_sections_form_id_foreign');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('ordering')->default(0);
            $table->unsignedInteger('columns')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_sections');
    }
};
