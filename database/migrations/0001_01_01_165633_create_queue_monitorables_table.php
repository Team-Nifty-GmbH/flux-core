<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('queue_monitorables')) {
            return;
        }

        Schema::create('queue_monitorables', function (Blueprint $table) {
            $table->unsignedBigInteger('queue_monitor_id');
            $table->string('queue_monitorable_type');
            $table->unsignedBigInteger('queue_monitorable_id');
            $table->boolean('notify_on_finish')->default(false);

            $table->primary(['queue_monitor_id', 'queue_monitorable_id', 'queue_monitorable_type']);
            $table->index(['queue_monitorable_type', 'queue_monitorable_id'], 'queue_monitorable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_monitorables');
    }
};
