<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Results\Result;
use FluxErp\Support\Widgets\Charts\LineChart;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;

class RevenuePurchasesProfitChart extends LineChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public function calculateByTimeFrame(): void
    {
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $baseQuery = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number');

        $revenue = Line::make($baseQuery->clone()->revenue())
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->sum('total_net_price');

        $purchases = Line::make($baseQuery->clone()->purchase())
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->sum('total_net_price');

        $purchasesData = $purchases->getCombinedData();
        $profit = [];
        foreach ($revenue->getCombinedData() as $key => $value) {
            $profit[$key] = (int) bcadd($value, data_get($purchasesData, $key, 0), 0);
        }
        $profit = Result::make(array_values($profit), array_keys($profit), null);

        $purchases->setData(
            array_map(fn ($value) => (int) bcmul($value, -1, 0), $purchases->getData())
        );

        $keys = array_unique(array_merge($revenue->getLabels(), $purchases->getLabels(), $profit->getLabels()));
        $revenue->mergeLabels($keys);
        $purchases->mergeLabels($keys);
        $profit->mergeLabels($keys);

        // remove all values that are zero in all series
        foreach ($keys as $key) {
            $data = [
                $revenue->getCombinedData()[$key] ?? 0,
                $purchases->getCombinedData()[$key] ?? 0,
                $profit->getCombinedData()[$key] ?? 0,
            ];

            if (array_sum($data) === 0) {
                $revenue->removeLabel($key);
                $purchases->removeLabel($key);
                $profit->removeLabel($key);
            }
        }

        $this->series = [
            [
                'name' => __('Revenue'),
                'color' => 'emerald',
                'data' => $revenue->getData(),
            ],
            [
                'name' => __('Purchases'),
                'color' => 'red',
                'data' => $purchases->getData(),
            ],
            [
                'name' => __('Profit'),
                'color' => 'indigo',
                'data' => $profit->getData(),
            ],
        ];

        $this->xaxis['categories'] = $keys;
    }
}
