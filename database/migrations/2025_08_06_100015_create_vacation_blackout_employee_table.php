<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_blackout_employee_department', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('employee_department_id')
                ->constrained('employee_departments', 'id', 'vac_blackout_emp_dept_foreign')
                ->cascadeOnDelete();
            $table->foreignId('vacation_blackout_id')
                ->constrained('vacation_blackouts', 'id', 'vac_blackout_vac_bl_foreign')
                ->cascadeOnDelete();

            $table->unique(['vacation_blackout_id', 'employee_department_id'], 'vac_blackout_dpt_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_blackout_employee_department');
    }
};
