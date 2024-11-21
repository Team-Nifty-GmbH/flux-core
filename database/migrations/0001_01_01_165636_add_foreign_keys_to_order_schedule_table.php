<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_schedule', function (Blueprint $table) {
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['schedule_id'])->references(['id'])->on('schedules')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_schedule', function (Blueprint $table) {
            $table->dropForeign('order_schedule_order_id_foreign');
            $table->dropForeign('order_schedule_schedule_id_foreign');
        });
    }
};
