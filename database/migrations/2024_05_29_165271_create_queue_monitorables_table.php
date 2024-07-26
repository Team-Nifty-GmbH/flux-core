<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('queue_monitorables', function (Blueprint $table) {
            $table->foreignId('queue_monitor_id')->constrained()->cascadeOnDelete();
            $table->morphs('queue_monitorable', 'queue_monitorable_index');
            $table->boolean('notify_on_finish')->default(false);

            $table->primary(
                [
                    'queue_monitor_id',
                    'queue_monitorable_id',
                    'queue_monitorable_type',
                ],
                'queue_monitorables_primary'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_monitorables');
    }
};
