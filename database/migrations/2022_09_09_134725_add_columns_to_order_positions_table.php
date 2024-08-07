<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->boolean('is_alternative')->default(false)->after('sort_number');

            $table->decimal('unit_gross_price', 40, 10)
                ->nullable()
                ->after('unit_price')
                ->comment('A decimal containing the price per unit net of the associated product.');

            $table->decimal('total_base_gross_price', 40, 10)
                ->nullable()
                ->after('purchase_price')
                ->comment('A decimal containing the order-position total price before any discounts.' .
                    ' Can be net or gross depending on the field is_net.');

            $table->decimal('total_base_net_price', 40, 10)
                ->nullable()
                ->after('total_base_gross_price')
                ->comment('A decimal containing the order-position total price before any discounts.' .
                    ' Can be net or gross depending on the field is_net.');

            $table->decimal('total_gross_price', 40, 10)
                ->nullable()
                ->after('total_base_net_price')
                ->comment('A decimal containing the order-position total price gross after all calculations.');

            $table->decimal('total_net_price', 40, 10)
                ->nullable()
                ->after('total_gross_price')
                ->comment('A decimal containing the order-position total price gross after all calculations.');

            $table->decimal('vat_price', 40, 10)
                ->nullable()
                ->after('total_net_price')
                ->comment('A decimal containing the tax for this order-position.');

            $table->renameColumn('unit_price', 'unit_net_price');
            $table->renameColumn('vat_rate', 'vat_rate_percentage');

            $table->decimal('discount_percentage', 11, 10)
                ->nullable()
                ->change();

            $table->json('product_prices')->nullable()->after('product_number');
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->renameColumn('unit_net_price', 'unit_price');
            $table->renameColumn('vat_rate_percentage', 'vat_rate');

            $table->decimal('discount_percentage', 8, 2)
                ->nullable()
                ->change();

            $table->dropColumn(
                [
                    'unit_gross_price',
                    'total_base_gross_price',
                    'total_base_net_price',
                    'total_gross_price',
                    'total_net_price',
                    'vat_price',
                    'is_alternative',
                    'product_prices',
                ]
            );
        });
    }
};
