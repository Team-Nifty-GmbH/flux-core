<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('address_invoice_id')->after('client_id');
            $table->unsignedBigInteger('address_delivery_id')->after('address_invoice_id');

            $table->foreign('address_invoice_id')->references('id')->on('addresses');
            $table->foreign('address_delivery_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_address_invoice_id_foreign');
            $table->dropColumn('address_invoice_id');

            $table->dropForeign('orders_address_delivery_id_foreign');
            $table->dropColumn('address_delivery_id');
        });
    }
};
