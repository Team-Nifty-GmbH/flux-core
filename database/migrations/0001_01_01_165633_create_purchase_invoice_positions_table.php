<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_invoice_positions')) {
            return;
        }

        Schema::create('purchase_invoice_positions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('purchase_invoice_id')->index('purchase_invoice_positions_purchase_invoice_id_foreign');
            $table->unsignedBigInteger('ledger_account_id')->nullable()->index('purchase_invoice_positions_ledger_account_id_foreign');
            $table->unsignedBigInteger('product_id')->nullable()->index('purchase_invoice_positions_product_id_foreign');
            $table->unsignedBigInteger('vat_rate_id')->nullable()->index('purchase_invoice_positions_vat_rate_id_foreign');
            $table->string('name')->nullable();
            $table->decimal('amount', 40, 10)->default(1);
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
