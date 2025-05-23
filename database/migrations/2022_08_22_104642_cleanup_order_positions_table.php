<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropColumn('total_price');
            $table->unsignedBigInteger('origin_position_id')->nullable()->after('order_id');
            $table->dropForeign('order_positions_price_id_foreign');
            $table->dropIndex('order_positions_price_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->decimal('total_price', 40, 10)
                ->nullable()
                ->after('purchase_price')
                ->comment('A decimal containing the order-position total price after all calculations. Can be net or gross depending on the field is_net.');
            $table->dropColumn('origin_position_id');
            $table->foreign('price_id')->references('id')->on('prices');
        });
    }
};
