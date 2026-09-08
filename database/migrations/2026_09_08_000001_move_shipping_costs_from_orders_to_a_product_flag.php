<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_shipping_item')
                ->default(false)
                ->after('is_shipping_free')
                ->index();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'shipping_costs_net_price',
                'shipping_costs_gross_price',
                'shipping_costs_vat_price',
                'shipping_costs_vat_rate_percentage',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('shipping_costs_net_price', 40, 10)
                ->nullable()
                ->after('payment_discount_percent')
                ->comment('A decimal containing the net price of shipping costs.');
            $table->decimal('shipping_costs_gross_price', 40, 10)
                ->nullable()
                ->after('shipping_costs_net_price')
                ->comment('A decimal containing the gross price of shipping costs.');
            $table->decimal('shipping_costs_vat_price', 40, 10)
                ->nullable()
                ->after('shipping_costs_gross_price')
                ->comment('A decimal containing the vat price of shipping costs.');
            $table->decimal('shipping_costs_vat_rate_percentage', 40, 10)
                ->nullable()
                ->after('shipping_costs_vat_price')
                ->comment('A decimal, containing the vat-rate in percent for the shipping costs, that is cached for easier and faster readability of this order.');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['is_shipping_item']);
            $table->dropColumn('is_shipping_item');
        });
    }
};
