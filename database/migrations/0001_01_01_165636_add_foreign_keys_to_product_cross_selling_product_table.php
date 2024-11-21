<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_cross_selling_product', function (Blueprint $table) {
            $table->foreign(['product_cross_selling_id'])->references(['id'])->on('product_cross_sellings')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_cross_selling_product', function (Blueprint $table) {
            $table->dropForeign('product_cross_selling_product_product_cross_selling_id_foreign');
            $table->dropForeign('product_cross_selling_product_product_id_foreign');
        });
    }
};
