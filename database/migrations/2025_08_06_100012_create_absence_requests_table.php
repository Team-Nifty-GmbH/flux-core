<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->foreignId('absence_type_id')
                ->constrained('absence_types');
            $table->foreignId('approved_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('rejected_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('substitute_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->string('status')->default('pending');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_requested', 5)->default(0);
            $table->decimal('work_hours_affected')->default(0);
            $table->decimal('work_days_affected', 10)->default(0);
            $table->date('sick_note_issued_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('substitute_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->boolean('is_emergency')->default(false);

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};
