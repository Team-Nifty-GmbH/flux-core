<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('address_product', function (Blueprint $table) {
            $table->foreign(['address_id'])->references(['id'])->on('addresses')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['product_id'])->references(['id'])->on('products')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('address_product', function (Blueprint $table) {
            $table->dropForeign('address_product_address_id_foreign');
            $table->dropForeign('address_product_product_id_foreign');
        });
    }
};
