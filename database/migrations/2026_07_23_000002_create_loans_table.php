<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();
            $table->foreignId('ledger_account_id')
                ->constrained('ledger_accounts')
                ->cascadeOnDelete();
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('number')->nullable();
            $table->decimal('amount', 40, 10);
            $table->decimal('interest_rate', 40, 10)->nullable();
            $table->string('repayment_type_enum');
            $table->unsignedInteger('number_of_installments');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->decimal('installment_amount', 40, 10)->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('loans');
    }
};
