<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_position_stock_posting', function (Blueprint $table) {
            $table->foreign(['order_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stock_posting_id'])->references(['id'])->on('stock_postings')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_position_stock_posting', function (Blueprint $table) {
            $table->dropForeign('order_position_stock_posting_order_position_id_foreign');
            $table->dropForeign('order_position_stock_posting_stock_posting_id_foreign');
        });
    }
};
