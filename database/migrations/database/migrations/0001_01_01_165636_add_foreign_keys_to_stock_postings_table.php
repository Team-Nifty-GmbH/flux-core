<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->foreign(['order_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'])->references(['id'])->on('stock_postings')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['serial_number_id'])->references(['id'])->on('serial_numbers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['warehouse_id'])->references(['id'])->on('warehouses')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->dropForeign('stock_postings_order_position_id_foreign');
            $table->dropForeign('stock_postings_parent_id_foreign');
            $table->dropForeign('stock_postings_product_id_foreign');
            $table->dropForeign('stock_postings_serial_number_id_foreign');
            $table->dropForeign('stock_postings_warehouse_id_foreign');
        });
    }
};
