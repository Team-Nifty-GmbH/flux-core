<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('address_product')) {
            return;
        }

        Schema::create('address_product', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('product_id')->index('address_product_product_id_foreign');

            $table->primary(['address_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_product');
    }
};
