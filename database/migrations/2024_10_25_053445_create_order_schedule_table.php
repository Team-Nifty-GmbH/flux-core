<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_schedule', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_schedule');
    }
};
