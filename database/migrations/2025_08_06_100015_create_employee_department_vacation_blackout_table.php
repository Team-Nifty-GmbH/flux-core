<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employee_department_vacation_blackout', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('employee_department_id')
                ->constrained(
                    'employee_departments',
                    'id',
                    'emp_dept_vac_blackout_employee_department_id_foreign'
                )
                ->cascadeOnDelete();
            $table->foreignId('vacation_blackout_id')
                ->constrained(
                    'vacation_blackouts',
                    'id',
                    'emp_dept_vac_blackout_vacation_blackout_id_foreign'
                )
                ->cascadeOnDelete();

            $table->unique(
                ['employee_department_id', 'vacation_blackout_id'],
                'employee_department_vacation_blackout_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_department_vacation_blackout');
    }
};
