<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('form_builder_sections', function (Blueprint $table) {
            $table->foreign(['form_id'])->references(['id'])->on('form_builder_forms')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_sections', function (Blueprint $table) {
            $table->dropForeign('form_builder_sections_form_id_foreign');
        });
    }
};
