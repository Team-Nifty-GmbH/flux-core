<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('job_batchables', function (Blueprint $table) {
            $table->foreign(['job_batch_id'])->references(['id'])->on('job_batches')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('job_batchables', function (Blueprint $table) {
            $table->dropForeign('job_batchables_job_batch_id_foreign');
        });
    }
};
