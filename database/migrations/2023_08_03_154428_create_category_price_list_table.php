<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_price_list', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('discount_id');

            $table->primary(['category_id', 'price_list_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->cascadeOnDelete();
            $table->foreign('discount_id')
                ->references('id')
                ->on('discounts')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_price_list');
    }
};
