<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_product')) {
            return;
        }

        Schema::create('client_product', function (Blueprint $table) {
            $table->bigIncrements('pivot_id');
            $table->unsignedBigInteger('client_id')->index('client_product_client_id_foreign');
            $table->unsignedBigInteger('product_id')->index('client_product_product_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_product');
    }
};
