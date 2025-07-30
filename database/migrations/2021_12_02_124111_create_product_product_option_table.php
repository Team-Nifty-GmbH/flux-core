<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_product_option', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_option_id');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('product_option_id')
                ->references('id')
                ->on('product_options')
                ->cascadeOnDelete();

            $table->unique(['product_id', 'product_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_option');
    }
};
