<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('work_time_models', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->string('name');
            $table->unsignedInteger('cycle_weeks')->default(1);
            $table->decimal('weekly_hours', 5, 2);
            $table->decimal('annual_vacation_days', 5, 2);
            $table->unsignedTinyInteger('work_days_per_week')->nullable();
            $table->decimal('max_overtime_hours', 8, 2)->nullable();
            $table->string('overtime_compensation_enum');

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('work_time_models');
    }
};
