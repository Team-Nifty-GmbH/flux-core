<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_product_option', function (Blueprint $table) {
            $table->dropForeign('product_product_option_product_id_foreign');
            $table->dropForeign('product_product_option_product_option_id_foreign');

            $table->dropPrimary('product_product_option_product_option_id_foreign');
        });

        Schema::table('product_product_option', function (Blueprint $table) {
            $table->id()->first();
            $table->unique(['product_id', 'product_option_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_option_id')
                ->references('id')
                ->on('product_options')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_product_option', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropForeign('product_product_option_product_option_id_foreign');
            $table->dropForeign('product_product_option_product_id_foreign');
            $table->dropUnique('product_product_option_product_id_product_option_id_unique');
        });

        Schema::table('product_product_option', function (Blueprint $table) {
            $table->primary(['product_id', 'product_option_id']);

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('product_option_id')->references('id')->on('product_options');
        });
    }
};
