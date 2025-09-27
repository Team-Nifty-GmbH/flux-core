<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employee_work_time_model', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('work_time_model_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->decimal('annual_vacation_days', 5, 2)->nullable();
            $table->text('note')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_work_time_model');
    }
};
