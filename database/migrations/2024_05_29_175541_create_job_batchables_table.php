<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('job_batchables', function (Blueprint $table): void {
            $table->string('job_batch_id');
            $table->morphs('job_batchable', 'job_batchable_index');
            $table->boolean('notify_on_finish')->default(false);

            $table->foreign('job_batch_id')
                ->references('id')
                ->on('job_batches')
                ->cascadeOnDelete();

            $table->primary(
                [
                    'job_batch_id',
                    'job_batchable_id',
                    'job_batchable_type',
                ],
                'job_batchables_primary'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batchables');
    }
};
