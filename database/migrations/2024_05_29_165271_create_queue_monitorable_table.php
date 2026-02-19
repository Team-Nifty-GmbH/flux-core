<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('queue_monitorable')) {
            return;
        }

        Schema::create('queue_monitorable', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('queue_monitor_id')->constrained('queue_monitors')->cascadeOnDelete();
            $table->morphs('queue_monitorable', 'queue_monitorable_index');
            $table->boolean('notify_on_finish')->default(false);

            $table->unique(
                [
                    'queue_monitor_id',
                    'queue_monitorable_type',
                    'queue_monitorable_id',
                ],
                'queue_monitorable_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_monitorable');
    }
};
