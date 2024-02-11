<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_positions', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('ledger_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vat_rate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->decimal('amount', 40, 10, true)->default(1);
            $table->decimal('unit_price', 40, 10, true)->nullable();
            $table->decimal('total_price', 40, 10, true)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_positions');
    }
};
