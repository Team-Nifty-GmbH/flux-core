<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_product_option')) {
            return;
        }

        Schema::create('product_product_option', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_option_id')->index('product_product_option_product_option_id_foreign');

            $table->unique(['product_id', 'product_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_option');
    }
};
