<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->foreignId('product_configuration_group_item_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_configuration_group_items', indexName: 'order_positions_pcg_item_id_foreign')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropForeign('order_positions_pcg_item_id_foreign');
            $table->dropColumn('product_configuration_group_item_id');
        });
    }
};
