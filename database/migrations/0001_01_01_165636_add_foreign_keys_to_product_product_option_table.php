<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_product_option', function (Blueprint $table) {
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['product_option_id'])->references(['id'])->on('product_options')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_product_option', function (Blueprint $table) {
            $table->dropForeign('product_product_option_product_id_foreign');
            $table->dropForeign('product_product_option_product_option_id_foreign');
        });
    }
};
