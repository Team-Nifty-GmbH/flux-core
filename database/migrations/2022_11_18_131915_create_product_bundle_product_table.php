<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_bundle_product', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')
                ->comment('Main bundle product containing other products.');
            $table->unsignedBigInteger('bundle_product_id')
                ->comment('Referenced product of the bundle.');
            $table->decimal('count', 40, 10)->default(1);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('bundle_product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundle_product');
    }
};
