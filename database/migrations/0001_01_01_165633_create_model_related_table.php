<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('model_related')) {
            return;
        }

        Schema::create('model_related', function (Blueprint $table) {
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('related_type');
            $table->unsignedBigInteger('related_id');

            $table->index(['model_type', 'model_id']);
            $table->index(['related_type', 'related_id']);
            $table->primary(['model_type', 'model_id', 'related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_related');
    }
};
