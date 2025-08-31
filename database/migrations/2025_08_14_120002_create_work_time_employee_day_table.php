<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('work_time_employee_day', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('work_time_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('employee_day_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['work_time_id', 'employee_day_id'], 'work_time_employee_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_time_employee_day');
    }
};
