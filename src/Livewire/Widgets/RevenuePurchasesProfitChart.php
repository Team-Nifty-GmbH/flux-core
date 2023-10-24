<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Livewire\Charts\BarChart;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevenuePurchasesProfitChart extends BarChart implements UserWidget
{
    public static function getLabel(): string
    {
        return Str::headline(class_basename(self::class));
    }

    public function calculateChart(): void
    {
        $baseQuery = Order::query()
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->where('invoice_date', '>=', now()->subYear());

        $monthlyRevenue = $this->getSum($baseQuery->clone()
            ->whereHas('orderType', function ($query) {
                $query->whereNotIn('order_type_enum', ['purchase', 'purchase-refund']);
            }));
        $monthlyPurchases = $this->getSum($baseQuery->clone()
            ->whereHas('orderType', function ($query) {
                $query->whereIn('order_type_enum', ['purchase', 'purchase-refund']);
            }));


        ksort($monthlyRevenue);
        ksort($monthlyPurchases);

        $monthlyProfit = [];
        foreach ($monthlyRevenue as $key => $value) {
            $monthlyProfit[$key] = bcadd($value, $monthlyPurchases[$key], 2);
        }

        $monthlyPurchases = array_map(fn($value) => bcmul($value, -1, 2), $monthlyPurchases);

        $this->series = [
            [
                'name' => __('Revenue'),
                'color' => 'emerald',
                'data' => array_values($monthlyRevenue),
                'sum' => array_sum($monthlyRevenue),
            ],
            [
                'name' => __('Purchases'),
                'color' => 'red',
                'data' => array_values($monthlyPurchases),
                'sum' => array_sum($monthlyPurchases)
            ],
            [
                'name' => __('Profit'),
                'color' => 'indigo',
                'data' => array_values($monthlyProfit),
                'sum' => array_sum($monthlyProfit)
            ]
        ];

        $this->xaxis['categories'] = array_keys($monthlyRevenue);
    }

    private function getSum(Builder $builder): array
    {
        return $builder->select(
            DB::raw('DATE_FORMAT(invoice_date, "%Y.%m") as month_year'),
            DB::raw('ROUND(SUM(total_net_price), 2) as total')
        )
            ->groupBy(DB::raw('YEAR(invoice_date)'), DB::raw('MONTH(invoice_date)'), 'month_year')
            ->get()
            ->pluck('total', 'month_year')
            ->toArray();
    }
}
