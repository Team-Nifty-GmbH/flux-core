<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_warehouse_bin', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('warehouse_bin_id')
                ->constrained('warehouse_bins')
                ->cascadeOnDelete();

            $table->boolean('is_fixed_location')
                ->default(false)
                ->comment('A fixed location is the picking bin of this product; without any assignment storage is chaotic.');
            $table->decimal('min_stock', 40, 10)->nullable();
            $table->decimal('max_stock', 40, 10)->nullable();
            $table->integer('sort_order')->default(0);

            $table->unique(['product_id', 'warehouse_bin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse_bin');
    }
};
