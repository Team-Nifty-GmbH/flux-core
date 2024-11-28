<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_bundle_product')) {
            return;
        }

        Schema::create('product_bundle_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index('product_bundle_product_product_id_foreign')->comment('Main bundle product containing other products.');
            $table->unsignedBigInteger('bundle_product_id')->index('product_bundle_product_bundle_product_id_foreign')->comment('Referenced product of the bundle.');
            $table->decimal('count', 40, 10)->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundle_product');
    }
};
