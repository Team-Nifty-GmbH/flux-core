<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('locks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('locks', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->string('authenticatable_type');
            $table->unsignedBigInteger('authenticatable_id');
            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');

            $table->unique(['model_type', 'model_id']);

            $table->index(['authenticatable_type', 'authenticatable_id']);
        });
    }
};
