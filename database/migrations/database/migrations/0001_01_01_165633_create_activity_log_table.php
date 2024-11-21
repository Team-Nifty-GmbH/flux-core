<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->json('properties')->nullable();
            $table->char('batch_uuid', 36)->nullable();
            $table->timestamps();

            $table->index(['log_name', 'event']);
            $table->index(['causer_type', 'causer_id'], 'causer');
            $table->index(['subject_type', 'subject_id'], 'subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
