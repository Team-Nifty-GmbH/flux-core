<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->foreign(['bundle_product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_bundle_product', function (Blueprint $table) {
            $table->dropForeign('product_bundle_product_bundle_product_id_foreign');
            $table->dropForeign('product_bundle_product_product_id_foreign');
        });
    }
};
