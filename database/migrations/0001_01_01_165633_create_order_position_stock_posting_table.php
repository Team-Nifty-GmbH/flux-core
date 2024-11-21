<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_position_stock_posting')) {
            return;
        }

        Schema::create('order_position_stock_posting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_position_id')->index('order_position_stock_posting_order_position_id_foreign');
            $table->unsignedBigInteger('stock_posting_id')->index('order_position_stock_posting_stock_posting_id_foreign');
            $table->decimal('reserved_amount', 40, 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_position_stock_posting');
    }
};
