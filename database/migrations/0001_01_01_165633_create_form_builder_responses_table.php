<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('form_builder_responses')) {
            return;
        }

        Schema::create('form_builder_responses', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('form_id')->index('form_builder_responses_form_id_foreign');
            $table->unsignedBigInteger('user_id')->nullable()->index('form_builder_responses_user_id_foreign');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_responses');
    }
};
