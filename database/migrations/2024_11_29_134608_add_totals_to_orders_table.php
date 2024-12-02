<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_base_discounted_net_price', 40, 10)
                ->nullable()
                ->after('total_base_gross_price');
            $table->decimal('total_base_discounted_gross_price', 40, 10)
                ->nullable()
                ->after('total_base_discounted_net_price');

            $table->decimal('total_discount_percentage', 40, 10)
                ->nullable()
                ->after('total_vats');
            $table->decimal('total_discount_currency', 40, 10)
                ->nullable()
                ->after('total_discount_percentage');

            $table->decimal('total_position_discount_percentage', 40, 10)
                ->nullable()
                ->after('total_discount_currency');
            $table->decimal('total_position_discount_currency', 40, 10)
                ->nullable()
                ->after('total_position_discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total_discount_percentage',
                'total_discount_currency',
                'total_base_discounted_gross_price',
                'total_base_discounted_net_price',
                'total_position_discount_percentage',
                'total_position_discount_currency',
            ]);
        });
    }
};
