<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_cross_selling_product', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_cross_selling_id')
                ->constrained('product_cross_sellings')
                ->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->unique(['product_cross_selling_id', 'product_id'], 'product_cross_selling_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cross_selling_product');
    }
};
