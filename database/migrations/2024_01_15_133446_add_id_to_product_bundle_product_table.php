<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->dropForeign('product_bundle_product_bundle_product_id_foreign');
            $table->dropForeign('product_bundle_product_product_id_foreign');
            $table->dropPrimary();
        });

        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->id()->first();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('bundle_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->dropColumn('id');

            $table->primary(['product_id', 'bundle_product_id']);
        });
    }
};
