<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('serial_number_range_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('order_position_id')->nullable();
            $table->string('serial_number');
            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');

            $table->foreign('serial_number_range_id')
                ->references('id')
                ->on('serial_number_ranges');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->foreign('order_position_id')->references('id')->on('order_positions');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
