<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employee_vacation_blackout', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();
            $table->foreignId('vacation_blackout_id')
                ->constrained('vacation_blackouts')
                ->cascadeOnDelete();

            $table->unique(['employee_id', 'vacation_blackout_id'], 'employee_vacation_blackout_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_vacation_blackout');
    }
};
