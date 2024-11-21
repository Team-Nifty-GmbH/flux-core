<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_cross_selling_product')) {
            return;
        }

        Schema::create('product_cross_selling_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_cross_selling_id');
            $table->unsignedBigInteger('product_id')->index('product_cross_selling_product_product_id_foreign');

            $table->unique(['product_cross_selling_id', 'product_id'], 'product_cross_selling_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cross_selling_product');
    }
};
