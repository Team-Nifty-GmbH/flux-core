<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('product_id');

            $table->primary(['address_id', 'product_id']);
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_product');
    }
};
