<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->after('client_id');

            $table->boolean('is_locked')->default(false)->after('has_logistic_notify_number')
                ->comment('If set to true this order cant be edited anymore, ' .
                    'this happens usually when the invoice was printed.');

            $table->string('invoice_number')->after('invoice_date')->unique()->nullable();

            $table->unsignedBigInteger('unit_price_price_list_id')->after('price_list_id')->comment(
                'Just for customer frontend purposes, the unit price is replaced with the value from ' .
                'this price_list_id price. This also triggers recalculation of the discount.'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'currency_id',
                    'unit_price_price_list_id',
                    'is_locked',
                    'invoice_number',
                ]
            );
        });
    }
};
