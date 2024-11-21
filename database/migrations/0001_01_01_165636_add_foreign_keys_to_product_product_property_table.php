<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_product_property', function (Blueprint $table) {
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['product_prop_id'])->references(['id'])->on('product_properties')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('product_product_property', function (Blueprint $table) {
            $table->dropForeign('product_product_property_product_id_foreign');
            $table->dropForeign('product_product_property_product_prop_id_foreign');
        });
    }
};
