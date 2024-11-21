<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_supplier')) {
            return;
        }

        Schema::create('product_supplier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('contact_id')->index('product_supplier_contact_id_foreign');
            $table->string('manufacturer_product_number')->nullable();
            $table->decimal('purchase_price', 40, 10)->nullable();

            $table->unique(['product_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
    }
};
