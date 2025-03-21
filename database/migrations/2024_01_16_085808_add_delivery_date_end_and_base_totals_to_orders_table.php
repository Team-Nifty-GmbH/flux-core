<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->date('system_delivery_date_end')->after('system_delivery_date')->nullable();

            $table->decimal('total_base_net_price', 40, 10)
                ->nullable()
                ->after('shipping_costs_vat_rate_percentage');
            $table->decimal('total_base_gross_price', 40, 10)
                ->nullable()
                ->after('total_base_net_price');

            $table->decimal('balance', 40, 10)
                ->nullable()
                ->after('total_vats');
        });

        $this->migrateTotals();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['system_delivery_date_end', 'total_base_net_price', 'total_base_gross_price']);
        });
    }

    private function migrateTotals(): void
    {
        // SET total_base_net_price
        DB::statement(
            'UPDATE orders
                JOIN (
                    SELECT order_id, SUM(total_base_net_price) AS totalSum
                    FROM order_positions
                    WHERE is_alternative = 0
                    AND deleted_at IS NULL
                    GROUP BY order_id
                ) op ON orders.id = op.order_id
                SET orders.total_base_net_price = COALESCE(op.totalSum, 0) + COALESCE(orders.shipping_costs_net_price, 0)'
        );

        // SET total_base_gross_price
        DB::statement(
            'UPDATE orders
                JOIN (
                    SELECT order_id, SUM(total_base_gross_price) AS totalSum
                    FROM order_positions
                    WHERE is_alternative = 0
                    AND deleted_at IS NULL
                    GROUP BY order_id
                ) op ON orders.id = op.order_id
                SET orders.total_base_gross_price = COALESCE(op.totalSum, 0) + COALESCE(orders.shipping_costs_gross_price, 0)'
        );

        // SET balance
        DB::statement(
            'UPDATE orders
                LEFT JOIN (
                    SELECT order_id, SUM(amount) AS totalSum
                    FROM transactions
                    GROUP BY order_id
                ) t ON orders.id = t.order_id
                SET orders.balance = ROUND(COALESCE(orders.total_gross_price, 0), 2) - COALESCE(t.totalSum, 0)
                WHERE orders.invoice_number IS NOT NULL'
        );
    }
};
