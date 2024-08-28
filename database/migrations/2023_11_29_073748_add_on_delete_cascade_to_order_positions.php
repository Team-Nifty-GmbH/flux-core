<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign('order_positions_parent_id_foreign');
            $table->foreign('parent_id')
                ->references('id')
                ->on('order_positions')
                ->cascadeOnDelete();
            $table->dropForeign('order_positions_order_id_foreign');
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign('order_positions_parent_id_foreign');
            $table->foreign('parent_id')
                ->references('id')
                ->on('order_positions');
            $table->dropForeign('order_positions_order_id_foreign');
            $table->foreign('order_id')
                ->references('id')
                ->on('orders');
        });
    }
};
