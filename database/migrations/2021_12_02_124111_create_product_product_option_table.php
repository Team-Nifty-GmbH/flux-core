<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductProductOptionTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_product_option', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_option_id');

            $table->primary(['product_id', 'product_option_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('product_option_id')->references('id')->on('product_options');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_option');
    }
}
