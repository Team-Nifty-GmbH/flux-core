<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        });

        DB::insert('INSERT INTO client_product (client_id, product_id) SELECT client_id, id FROM products');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable();
        });

        DB::statement('UPDATE products p JOIN client_product cp ON p.id = cp.product_id SET p.client_id = cp.client_id');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('client_id')->on('clients')->references('id')->cascadeOnDelete();
        });

        Schema::dropIfExists('client_product');
    }
};
