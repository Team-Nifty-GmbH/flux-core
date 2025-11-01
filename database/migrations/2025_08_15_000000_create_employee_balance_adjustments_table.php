<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('employee_balance_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);

            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->date('effective_date');
            $table->string('reason');
            $table->text('description')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->index(['type', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_balance_adjustments');
    }
};
