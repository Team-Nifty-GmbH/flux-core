<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_address_type_order', function (Blueprint $table): void {
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('address_type_id');
            $table->unsignedBigInteger('order_id');
            $table->json('address')->nullable();

            $table->primary(['address_id', 'address_type_id', 'order_id'], 'id');
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->cascadeOnDelete();
            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types')
                ->cascadeOnDelete();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_address_type_order');
    }
};
