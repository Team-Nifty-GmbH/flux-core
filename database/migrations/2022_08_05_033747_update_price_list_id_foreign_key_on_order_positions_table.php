<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropForeign('order_positions_price_list_id_foreign');
            $table->dropIndex('order_positions_price_list_id_foreign');

            $table->foreign('price_list_id')->references('id')->on('price_lists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropForeign('order_positions_price_list_id_foreign');
            $table->dropIndex('order_positions_price_list_id_foreign');

            $table->foreign('price_list_id')->references('id')->on('prices');
        });
    }
};
