<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Charts\BarChart;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevenuePurchasesProfitChart extends BarChart implements UserWidget
{
    public static function getLabel(): string
    {
        return __(Str::headline(class_basename(self::class)));
    }

    public function calculateChart(): void
    {
        $baseQuery = Order::query()
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number');

        $timeFrame = TimeFrameEnum::fromName($this->timeFrame);
        $parameters = $timeFrame->dateQueryParameters('invoice_date');
        if ($parameters && count($parameters) > 0) {
            if ($parameters['operator'] === 'between') {
                $baseQuery->whereBetween($parameters['column'], $parameters['value']);
            } else {
                $baseQuery->where(...array_values($parameters));
            }
        }

        $totalRevenue = $this->getSum(
            $baseQuery->clone()
                ->whereHas('orderType', function ($query) {
                    $query->whereNotIn('order_type_enum', ['purchase', 'purchase-refund']);
                }),
            $timeFrame,
            'invoice_date',
            'total_net_price'
        );
        $totalPurchases = $this->getSum(
            $baseQuery->clone()
                ->whereHas('orderType', function ($query) {
                    $query->whereIn('order_type_enum', ['purchase', 'purchase-refund']);
                }),
            $timeFrame,
            'invoice_date',
            'total_net_price'
        );

        $totalProfit = [];
        foreach ($totalRevenue as $key => $value) {
            $totalProfit[$key] = (int) bcadd($value, $totalPurchases[$key] ?? 0, 2);
        }

        $totalPurchases = array_map(fn ($value) => (int) bcmul($value, -1, 2), $totalPurchases);
        $totalRevenue = array_map(fn ($value) => (int) $value, $totalRevenue);

        $keys = array_unique(array_merge(array_keys($totalRevenue), array_keys($totalPurchases), array_keys($totalProfit)));
        foreach ($keys as $key) {
            $totalRevenue[$key] ??= 0;
            $totalPurchases[$key] ??= 0;
            $totalProfit[$key] ??= 0;
        }

        // remove all values that are zero in all series
        foreach ($totalRevenue as $key => $value) {
            if ($value === 0 && $totalPurchases[$key] === 0 && $totalProfit[$key] === 0) {
                unset($totalRevenue[$key], $totalPurchases[$key], $totalProfit[$key]);
            }
        }

        ksort($totalRevenue);
        ksort($totalPurchases);
        ksort($totalProfit);

        $this->series = [
            [
                'name' => __('Revenue'),
                'color' => 'emerald',
                'data' => array_values($totalRevenue),
            ],
            [
                'name' => __('Purchases'),
                'color' => 'red',
                'data' => array_values($totalPurchases),
            ],
            [
                'name' => __('Profit'),
                'color' => 'indigo',
                'data' => array_values($totalProfit),
            ],
        ];

        $this->xaxis['categories'] = array_keys($totalRevenue);
    }
}
