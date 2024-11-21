<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            return;
        }

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('cart_id')->index('cart_items_cart_id_foreign');
            $table->unsignedBigInteger('product_id')->nullable()->index('cart_items_product_id_foreign');
            $table->unsignedBigInteger('vat_rate_id')->index('cart_items_vat_rate_id_foreign');
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
