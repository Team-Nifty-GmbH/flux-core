<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('form_builder_fields', function (Blueprint $table) {
            $table->foreign(['section_id'])->references(['id'])->on('form_builder_sections')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_fields', function (Blueprint $table) {
            $table->dropForeign('form_builder_fields_section_id_foreign');
        });
    }
};
