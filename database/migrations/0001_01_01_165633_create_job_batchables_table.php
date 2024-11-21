<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_batchables')) {
            return;
        }

        Schema::create('job_batchables', function (Blueprint $table) {
            $table->string('job_batch_id');
            $table->string('job_batchable_type');
            $table->unsignedBigInteger('job_batchable_id');
            $table->boolean('notify_on_finish')->default(false);

            $table->index(['job_batchable_type', 'job_batchable_id'], 'job_batchable_index');
            $table->primary(['job_batch_id', 'job_batchable_id', 'job_batchable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batchables');
    }
};
