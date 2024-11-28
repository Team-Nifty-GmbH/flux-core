<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_schedule')) {
            return;
        }

        Schema::create('order_schedule', function (Blueprint $table) {
            $table->bigIncrements('pivot_id');
            $table->unsignedBigInteger('order_id')->index('order_schedule_order_id_foreign');
            $table->unsignedBigInteger('schedule_id')->index('order_schedule_schedule_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_schedule');
    }
};
