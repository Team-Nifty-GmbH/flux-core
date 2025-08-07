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
            $table->unsignedInteger('annual_vacation_days');
            $table->decimal('max_overtime_hours', 6, 2)->nullable();
            $table->enum('overtime_compensation', ['time_off', 'payment', 'both'])->default('time_off');
            $table->boolean('has_core_hours')->default(false);
            $table->time('core_hours_start')->nullable();
            $table->time('core_hours_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
            
            $table->unique(['uuid']);
            $table->index(['client_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_time_models');
    }
};