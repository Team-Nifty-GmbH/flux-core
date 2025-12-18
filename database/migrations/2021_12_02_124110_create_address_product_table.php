<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_product', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->unique(['address_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_product');
    }
};
