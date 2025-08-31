<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_blackout_employee', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('vacation_blackout_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['vacation_blackout_id', 'employee_id'], 'vac_blackout_emp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_blackout_employee');
    }
};
