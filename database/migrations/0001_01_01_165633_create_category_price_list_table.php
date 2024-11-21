<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('category_price_list')) {
            return;
        }

        Schema::create('category_price_list', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('price_list_id')->index('category_price_list_price_list_id_foreign');
            $table->unsignedBigInteger('discount_id')->index('category_price_list_discount_id_foreign');

            $table->primary(['category_id', 'price_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_price_list');
    }
};
