<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereNotNull('total_vats')
            ->update([
                'total_vats' => DB::raw("
                    (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'vat_rate_percentage', vat_data.vat_rate_percentage,
                                'total_vat_price', vat_data.total_vat_price,
                                'total_net_price', vat_data.total_net_price
                            )
                        )
                        FROM (
                            SELECT
                                op.vat_rate_percentage,
                                SUM(op.vat_price) +
                                    CASE
                                        WHEN op.vat_rate_percentage = 0.19
                                        THEN COALESCE(o.shipping_costs_vat_price, 0)
                                        ELSE 0
                                    END as total_vat_price,
                                SUM(op.total_net_price) +
                                    CASE
                                        WHEN op.vat_rate_percentage = 0.19
                                        THEN COALESCE(o.shipping_costs_net_price, 0)
                                        ELSE 0
                                    END as total_net_price
                            FROM order_positions op
                            JOIN orders o ON o.id = op.order_id
                            WHERE o.id = orders.id
                            WHERE op.is_alternative != 1
                            GROUP BY op.order_id, op.vat_rate_percentage
                        ) as vat_data
                    )
                "),
            ]);
    }

    public function down(): void {}
};
