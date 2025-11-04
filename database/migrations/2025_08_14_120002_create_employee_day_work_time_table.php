<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employee_day_work_time', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('employee_day_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('work_time_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['employee_day_id', 'work_time_id'], 'employee_day_work_time_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_day_work_time');
    }
};
