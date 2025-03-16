<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductProductPropertiesTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_product_property', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_prop_id');
            $table->text('value')->nullable();

            $table->primary(['product_id', 'product_prop_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('product_prop_id')->references('id')->on('product_properties');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_property');
    }
}
