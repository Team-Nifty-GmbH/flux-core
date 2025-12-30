<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_product_property')) {
            return;
        }

        Schema::create('product_product_property', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_property_id')->constrained('product_properties')->cascadeOnDelete();
            $table->text('value')->nullable();

            $table->unique(['product_id', 'product_property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_product_property');
    }
};
