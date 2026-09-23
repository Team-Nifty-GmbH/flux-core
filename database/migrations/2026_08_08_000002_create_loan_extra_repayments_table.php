<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('loan_extra_repayments', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('loan_id')
                ->constrained('loans')
                ->cascadeOnDelete();
            $table->date('executed_at');
            $table->decimal('amount', 40, 10);
            $table->string('schedule_adjustment_type_enum');
            $table->string('note')->nullable();
            $table->decimal('interest_saved', 40, 10)->nullable();
            $table->unsignedInteger('installments_saved')->nullable();
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
        Schema::dropIfExists('loan_extra_repayments');
    }
};
