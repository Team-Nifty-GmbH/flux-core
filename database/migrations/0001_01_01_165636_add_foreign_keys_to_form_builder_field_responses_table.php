<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('form_builder_field_responses', function (Blueprint $table) {
            $table->foreign(['field_id'])->references(['id'])->on('form_builder_fields')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['form_id'])->references(['id'])->on('form_builder_forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['response_id'])->references(['id'])->on('form_builder_responses')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_field_responses', function (Blueprint $table) {
            $table->dropForeign('form_builder_field_responses_field_id_foreign');
            $table->dropForeign('form_builder_field_responses_form_id_foreign');
            $table->dropForeign('form_builder_field_responses_response_id_foreign');
        });
    }
};
