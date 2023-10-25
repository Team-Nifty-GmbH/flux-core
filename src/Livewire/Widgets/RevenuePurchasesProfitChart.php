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
        return Str::headline(class_basename(self::class));
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

        $monthlyRevenue = $this->getSum($baseQuery->clone()
            ->whereHas('orderType', function ($query) {
                $query->whereNotIn('order_type_enum', ['purchase', 'purchase-refund']);
            }), $timeFrame);
        $monthlyPurchases = $this->getSum($baseQuery->clone()
            ->whereHas('orderType', function ($query) {
                $query->whereIn('order_type_enum', ['purchase', 'purchase-refund']);
            }), $timeFrame);

        $monthlyProfit = [];
        foreach ($monthlyRevenue as $key => $value) {
            $monthlyProfit[$key] = (int) bcadd($value, $monthlyPurchases[$key] ?? 0, 2);
        }

        $monthlyPurchases = array_map(fn ($value) => (int) bcmul($value, -1, 2), $monthlyPurchases);
        $monthlyRevenue = array_map(fn ($value) => (int) $value, $monthlyRevenue);

        $keys = array_unique(array_merge(array_keys($monthlyRevenue), array_keys($monthlyPurchases), array_keys($monthlyProfit)));
        foreach ($keys as $key) {
            $monthlyRevenue[$key] ??= 0;
            $monthlyPurchases[$key] ??= 0;
            $monthlyProfit[$key] ??= 0;
        }

        // remove all values that are zero in all series
        foreach ($monthlyRevenue as $key => $value) {
            if ($value === 0 && $monthlyPurchases[$key] === 0 && $monthlyProfit[$key] === 0) {
                unset($monthlyRevenue[$key]);
                unset($monthlyPurchases[$key]);
                unset($monthlyProfit[$key]);
            }
        }

        ksort($monthlyRevenue);
        ksort($monthlyPurchases);
        ksort($monthlyProfit);

        $this->series = [
            [
                'name' => __('Revenue'),
                'color' => 'emerald',
                'data' => array_values($monthlyRevenue),
            ],
            [
                'name' => __('Purchases'),
                'color' => 'red',
                'data' => array_values($monthlyPurchases),
            ],
            [
                'name' => __('Profit'),
                'color' => 'indigo',
                'data' => array_values($monthlyProfit),
            ],
        ];

        $this->xaxis['categories'] = array_keys($monthlyRevenue);
    }

    private function getSum(Builder $builder, TimeFrameEnum $timeFrameEnum): array
    {
        return $timeFrameEnum
            ->groupQuery($builder, 'invoice_date')
            ->addSelect(DB::raw('ROUND(SUM(total_net_price), 2) as total'))
            ->get()
            ->pluck('total', 'group_key')
            ->toArray();
    }
}
