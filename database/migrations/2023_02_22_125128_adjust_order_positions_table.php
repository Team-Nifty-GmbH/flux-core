<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->decimal('amount_bundle', 40, 10)->after('amount')->nullable();
        });

        $this->multiplyOrderPositions();

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropColumn('is_positive_operator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropColumn('amount_bundle');
            $table->boolean('is_positive_operator')->after('is_bundle_position')->default(1);
        });

        DB::statement('UPDATE order_positions SET is_positive_operator = 1 WHERE total_base_gross_price > 0');
        $this->multiplyOrderPositions();
    }

    private function multiplyOrderPositions(): void
    {
        DB::statement('UPDATE order_positions SET
                           total_base_gross_price = total_base_gross_price * -1,
                           total_base_net_price = total_base_net_price * -1,
                           total_gross_price = total_gross_price * -1,
                           total_net_price = total_net_price * -1,
                           vat_price = vat_price * -1,
                           unit_net_price = unit_net_price * -1,
                           unit_gross_price = unit_gross_price * -1
                       WHERE is_positive_operator = 0'
        );

        DB::statement('UPDATE orders SET
                           total_gross_price = (SELECT SUM(total_gross_price) FROM order_positions WHERE order_id = orders.id AND is_alternative = 0),
                           total_net_price = (SELECT SUM(total_net_price) FROM order_positions WHERE order_id = orders.id AND is_alternative = 0)
                       WHERE id IN (SELECT order_id FROM order_positions WHERE is_positive_operator = 0 AND is_alternative = 0)');

        // SET total_vats
        $vats = DB::table('orders')
            ->selectRaw('orders.id, shipping_costs_vat_rate_percentage, shipping_costs_vat_price, ' .
                'SUM(vat_price) as total_vat_price, vat_rate_percentage')
            ->join('order_positions', 'orders.id', '=', 'order_positions.order_id')
            ->where('is_positive_operator', false)
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
