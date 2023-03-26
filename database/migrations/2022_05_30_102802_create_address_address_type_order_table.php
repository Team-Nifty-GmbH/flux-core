<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressAddressTypeOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_address_type_order', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')
                ->comment('A unique identifier number for the table orders.');
            $table->unsignedBigInteger('address_id')
                ->comment('A unique identifier number for the table addresses.');
            $table->unsignedBigInteger('address_type_id')
                ->comment('A unique identifier number for the table address types.');

            $table->primary(['address_id', 'address_type_id', 'order_id'], 'id');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('address_type_id')->references('id')->on('address_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_address_type_order');
    }
}
