<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_product_property')) {
            return;
        }

        Schema::create('product_product_property', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_prop_id')->index('product_product_property_product_prop_id_foreign');
            $table->text('value')->nullable();

            $table->primary(['product_id', 'product_prop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_property');
    }
};
