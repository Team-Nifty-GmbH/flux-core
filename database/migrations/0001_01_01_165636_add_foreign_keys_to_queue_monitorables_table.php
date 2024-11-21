<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_monitorables', function (Blueprint $table) {
            $table->foreign(['queue_monitor_id'])->references(['id'])->on('queue_monitors')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('queue_monitorables', function (Blueprint $table) {
            $table->dropForeign('queue_monitorables_queue_monitor_id_foreign');
        });
    }
};
