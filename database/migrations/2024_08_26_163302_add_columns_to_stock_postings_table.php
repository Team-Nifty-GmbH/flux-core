<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('product_id')
                ->constrained('stock_postings')
                ->nullOnDelete();
            $table->foreignId('order_position_id')
                ->nullable()
                ->after('parent_id')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('serial_number_id')
                ->nullable()
                ->after('order_position_id')
                ->constrained()
                ->nullOnDelete();

            $table->decimal('remaining_stock', 40, 10)->after('stock')->nullable();
            $table->decimal('reserved_stock', 40, 10)->after('remaining_stock')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['order_position_id']);
            $table->dropForeign(['serial_number_id']);

            $table->dropColumn([
                'order_position_id',
                'serial_number_id',
                'remaining_stock',
                'reserved_stock',
            ]);
        });
    }
};
