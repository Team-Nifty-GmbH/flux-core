<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoice_positions', function (Blueprint $table) {
            $table->foreign(['ledger_account_id'])->references(['id'])->on('ledger_accounts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['purchase_invoice_id'])->references(['id'])->on('purchase_invoices')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['vat_rate_id'])->references(['id'])->on('vat_rates')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoice_positions', function (Blueprint $table) {
            $table->dropForeign('purchase_invoice_positions_ledger_account_id_foreign');
            $table->dropForeign('purchase_invoice_positions_product_id_foreign');
            $table->dropForeign('purchase_invoice_positions_purchase_invoice_id_foreign');
            $table->dropForeign('purchase_invoice_positions_vat_rate_id_foreign');
        });
    }
};
