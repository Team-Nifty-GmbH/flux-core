<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_builder_responses', function (Blueprint $table) {
            $table->foreign(['form_id'])->references(['id'])->on('form_builder_forms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_responses', function (Blueprint $table) {
            $table->dropForeign('form_builder_responses_form_id_foreign');
            $table->dropForeign('form_builder_responses_user_id_foreign');
        });
    }
};
