<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('serial_numbers', function (Blueprint $table): void {
            $table->string('supplier_serial_number')->nullable()->after('serial_number');

            $table->dropForeign(['product_id']);
            $table->dropForeign(['address_id']);
            $table->dropForeign(['order_position_id']);

            $table->dropColumn(['product_id', 'address_id', 'order_position_id']);
        });
    }

    public function down(): void
    {
        Schema::table('serial_numbers', function (Blueprint $table): void {
            $table->dropColumn('supplier_serial_number');

            $table->unsignedBigInteger('product_id')->nullable()->after('serial_number_range_id');
            $table->unsignedBigInteger('address_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('order_position_id')->nullable()->after('address_id');

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('order_position_id')->references('id')->on('order_positions');
        });
    }
};
