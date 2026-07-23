<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('loan_installments', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('loan_id')
                ->constrained('loans')
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->date('due_date');
            $table->decimal('principal_amount', 40, 10);
            $table->decimal('interest_amount', 40, 10);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
    }
};
