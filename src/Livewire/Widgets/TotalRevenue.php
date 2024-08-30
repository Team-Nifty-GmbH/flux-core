<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Results\Result;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\Charts\LineChart;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;

class TotalRevenue extends LineChart
{
    use MoneyChartFormattingTrait, Widgetable;

    public function calculateByTimeFrame(): void
    {
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $query = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->revenue();

        $metric = Line::make($query)
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start);
        $previousMetric = Line::make($query)
            ->setDateColumn('invoice_date')
            ->setEndingDate($metric->previousRange()[1])
            ->setStartingDate($metric->previousRange()[0])
            ->setRange(TimeFrameEnum::Custom);

        $growth = Value::make($query)
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->sum('total_net_price');

        /** @var Result $revenue */
        $revenue = $metric->sumByRange('total_net_price');
        /** @var Result $previousRevenue */
        $previousRevenue = $previousMetric->sumByRange('total_net_price');

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
