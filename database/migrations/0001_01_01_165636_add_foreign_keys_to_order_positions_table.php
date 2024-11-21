<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ledger_account_id'])->references(['id'])->on('ledger_accounts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['origin_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['price_list_id'])->references(['id'])->on('price_lists')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['supplier_contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['vat_rate_id'])->references(['id'])->on('vat_rates')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['warehouse_id'])->references(['id'])->on('warehouses')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign('order_positions_client_id_foreign');
            $table->dropForeign('order_positions_ledger_account_id_foreign');
            $table->dropForeign('order_positions_order_id_foreign');
            $table->dropForeign('order_positions_origin_position_id_foreign');
            $table->dropForeign('order_positions_parent_id_foreign');
            $table->dropForeign('order_positions_price_list_id_foreign');
            $table->dropForeign('order_positions_product_id_foreign');
            $table->dropForeign('order_positions_supplier_contact_id_foreign');
            $table->dropForeign('order_positions_vat_rate_id_foreign');
            $table->dropForeign('order_positions_warehouse_id_foreign');
        });
    }
};
