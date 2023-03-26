<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_net_price', 40, 10)
                ->default(0)
                ->after('margin');
            $table->decimal('total_gross_price', 40, 10)
                ->default(0)
                ->after('total_net_price');
            $table->json('total_vats')
                ->nullable()
                ->after('total_gross_price');
        });

        $this->migrateTotals();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_net_price', 'total_gross_price', 'total_vats']);
        });
    }

    private function migrateTotals(): void
    {
        // SET total_net_price
        DB::statement(
            'UPDATE orders
                    JOIN (
                        SELECT order_id, SUM(total_net_price) AS totalSum
                        FROM order_positions
                        WHERE is_alternative = 0
                        GROUP BY order_id
                    ) op ON orders.id = op.order_id
                    SET orders.total_net_price = COALESCE(op.totalSum, 0) + COALESCE(orders.shipping_costs_net_price, 0)'
        );

        // SET total_gross_price
        DB::statement(
            'UPDATE orders
                    JOIN (
                        SELECT order_id, SUM(total_gross_price) AS totalSum
                        FROM order_positions
                        WHERE is_alternative = 0
                        GROUP BY order_id
                    ) op ON orders.id = op.order_id
                    SET orders.total_gross_price = COALESCE(op.totalSum, 0) + COALESCE(orders.shipping_costs_gross_price, 0)'
        );

        // SET total_vats
        $vats = DB::table('orders')
            ->selectRaw('orders.id, shipping_costs_vat_rate_percentage, shipping_costs_vat_price, ' .
                'SUM(vat_price) as total_vat_price, vat_rate_percentage')
            ->join('order_positions', 'orders.id', '=', 'order_positions.order_id')
            ->where('is_alternative', false)
            ->whereNotNull('vat_rate_percentage')
            ->groupBy(
                'orders.id',
                'shipping_costs_vat_rate_percentage',
                'shipping_costs_vat_price',
                'vat_rate_percentage'
            )
            ->orderBy('orders.id')
            ->orderBy('vat_rate_percentage')
            ->get();

        $groupedVats = $vats->groupBy('id');

        foreach ($groupedVats as $id => $order) {
            $totalVats = $order->map(
                function ($item) {
                    $item = collect($item);

                    return $item->only('vat_rate_percentage', 'total_vat_price');
                })
                ->keyBy('vat_rate_percentage');

            if ($sVatRate = $order[0]->shipping_costs_vat_rate_percentage) {
                $totalVats->put(
                    $sVatRate,
                    [
                        'total_vat_price' => bcadd(
                            $order[0]->shipping_costs_vat_price,
                            $totalVats->get($sVatRate)['total_vat_price'] ?? 0,
                            9
                        ),
                        'vat_rate_percentage' => $sVatRate,
                    ]
                );
            }

            DB::table('orders')
                ->where('id', $id)
                ->update(['total_vats' => $totalVats->sortBy('vat_rate_percentage')->values()->toArray()]);
        }
    }
};
