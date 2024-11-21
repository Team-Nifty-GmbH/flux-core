<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('communicatable')) {
            return;
        }

        Schema::create('communicatable', function (Blueprint $table) {
            $table->id();
            $table->string('communicatable_type');
            $table->unsignedBigInteger('communicatable_id');
            $table->unsignedBigInteger('communication_id')->index('communicatable_communication_id_foreign');

            $table->unique(['communicatable_type', 'communicatable_id', 'communication_id'], 'communicatable_type_id_communication_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communicatable');
    }
};
