<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('category_price_list', function (Blueprint $table): void {
            $table->id('pivot_id');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();

            $table->unique(['category_id', 'price_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_price_list');
    }
};
