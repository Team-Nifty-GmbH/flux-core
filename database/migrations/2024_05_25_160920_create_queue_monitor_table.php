<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_monitors', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_uuid')->nullable();
            $table->string('job_batch_id')->nullable();
            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();

            $table->string('state')->default('running');
            $table->dateTime('queued_at')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->string('started_at_exact')->nullable();

            $table->timestamp('finished_at')->nullable();
            $table->string('finished_at_exact')->nullable();

            $table->integer('attempt')->default(0);
            $table->boolean('retried')->default(false);
            $table->decimal('progress', 11, 10)->default(0);

            $table->longText('exception')->nullable();
            $table->text('exception_message')->nullable();
            $table->text('exception_class')->nullable();

            $table->longText('data')->nullable();
            $table->mediumText('accept')->nullable();
            $table->mediumText('reject')->nullable();
            $table->mediumText('message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_monitors');
    }
};
