<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressAddressTypeTable extends Migration
{
    public function up(): void
    {
        Schema::create('address_address_type', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')
                ->comment('A unique identifier number for the table addresses.');
            $table->unsignedBigInteger('address_type_id')
                ->comment('A unique identifier number for the table address types.');

            $table->primary(['address_id', 'address_type_id']);
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('address_type_id')->references('id')->on('address_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_address_type');
    }
}
