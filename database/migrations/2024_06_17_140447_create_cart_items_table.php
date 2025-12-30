<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id();
            $table->uuid();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('vat_rate_id')->constrained('vat_rates')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->decimal('amount', 40, 10);
            $table->decimal('price', 40, 10);
            $table->decimal('total_net', 40, 10);
            $table->decimal('total_gross', 40, 10);
            $table->decimal('total', 40, 10);
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
