<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Results\Result;
use FluxErp\Support\Metrics\Trend;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\Charts\LineChart;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;

class AverageOrderValue extends LineChart
{
    use MoneyChartFormattingTrait, Widgetable;

    public function calculateChart(): void
    {
        $query = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->revenue();

        $metric = Line::make($query)
            ->dateColumn('invoice_date')
            ->range($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start);
        $previousMetric = Trend::make($query)
            ->dateColumn('invoice_date')
            ->setEndingDate($metric->previousRange()[1])
            ->setStartingDate($metric->previousRange()[0])
            ->range(TimeFrameEnum::Custom);

        $growth = Value::make($query)
            ->range($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->dateColumn('invoice_date')
            ->withGrowthRate()
            ->avg('total_net_price');

        /** @var Result $revenue */
        $revenue = $metric->avgByRange('total_net_price');
        /** @var Result $previousRevenue */
        $previousRevenue = $previousMetric->avgByRange('total_net_price');

        $this->series = [
            [
                'name' => __('Previous Period'),
                'color' => 'gray-300',
                'data' => $previousRevenue->getData(),
                'hideFromTotals' => true,
            ],
            [
                'name' => static::getLabel(),
                'color' => 'indigo',
                'data' => $revenue->getData(),
                'growthRate' => $growth->getGrowthRate(),
            ],
        ];

        $this->xaxis['categories'] = $revenue->getLabels();
    }
}
