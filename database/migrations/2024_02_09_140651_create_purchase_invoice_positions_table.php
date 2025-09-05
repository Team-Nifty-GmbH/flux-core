<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_positions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('ledger_account_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->nullOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->foreignId('purchase_invoice_id')
                ->constrained('purchase_invoices')
                ->cascadeOnDelete();
            $table->foreignId('vat_rate_id')
                ->nullable()
                ->constrained('vat_rates')
                ->nullOnDelete();
            $table->string('name')->nullable();
            $table->decimal('amount', 40, 10);
            $table->decimal('unit_price', 40, 10)->nullable();
            $table->decimal('total_price', 40, 10)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_positions');
    }
};
