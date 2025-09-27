<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employee_days', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('holiday_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('date')->index();
            $table->decimal('target_hours', 8, 2)->default(0);
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->decimal('break_minutes', 8, 2)->default(0);
            $table->decimal('vacation_hours_used', 8, 2)->default(0);
            $table->decimal('vacation_days_used', 8, 2)->default(0);
            $table->decimal('sick_hours_used', 8, 2)->default(0);
            $table->decimal('sick_days_used', 8, 2)->default(0);
            $table->decimal('plus_minus_overtime_hours', 8, 2)->default(0);
            $table->decimal('plus_minus_absence_hours', 8, 2)->default(0);

            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_work_day')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_days');
    }
};
