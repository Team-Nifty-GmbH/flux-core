<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_costs_vat_rate_percentage', 40, 10)
                ->nullable()
                ->after('shipping_costs')
                ->comment('A decimal, containing the vat-rate in percent for the shipping costs, that is cached for easier and faster readability of this order.');
            $table->decimal('shipping_costs_vat_price', 40, 10)
                ->nullable()
                ->after('shipping_costs')
                ->comment('A decimal containing the vat price of shipping costs.');
            $table->decimal('shipping_costs_gross_price', 40, 10)
                ->nullable()
                ->after('shipping_costs')
                ->comment('A decimal containing the gross price of shipping costs.');

            $table->decimal('shipping_costs', 40, 10)
                ->default(null)
                ->nullable()
                ->comment('A decimal containing the net price of shipping costs.')
                ->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('shipping_costs', 'shipping_costs_net_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_costs_net_price', 40, 10)
                ->default(0)
                ->nullable(false)
                ->comment(null)
                ->change();

            $table->dropColumn('shipping_costs_gross_price');
            $table->dropColumn('shipping_costs_vat_price');
            $table->dropColumn('shipping_costs_vat_rate_percentage');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('shipping_costs_net_price', 'shipping_costs');
        });
    }
};
