<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->foreignId('warehouse_bin_id')
                ->nullable()
                ->after('warehouse_id')
                ->comment('Null means the stock sits in the warehouse without a known bin.')
                ->constrained('warehouse_bins')
                ->nullOnDelete();
            $table->foreignId('lot_id')
                ->nullable()
                ->after('warehouse_bin_id')
                ->constrained('lots')
                ->nullOnDelete();

            $table->index(['warehouse_bin_id', 'product_id']);
            $table->index(['warehouse_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_postings', function (Blueprint $table): void {
            $table->dropIndex(['warehouse_id', 'product_id']);
            $table->dropIndex(['warehouse_bin_id', 'product_id']);
            $table->dropConstrainedForeignId('lot_id');
            $table->dropConstrainedForeignId('warehouse_bin_id');
        });
    }
};
