<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Value;
use FluxErp\Support\Widgets\Charts\LineChart;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Renderless;

class TotalOrdersCount extends LineChart
{
    use IsTimeFrameAwareWidget, Widgetable;

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }

    #[Renderless]
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
            ->setEndingDate($this->end?->endOfDay())
            ->setStartingDate($this->start?->startOfDay());
        $previousMetric = Line::make($query)
            ->setDateColumn('invoice_date')
            ->setEndingDate($metric->previousRange()[1])
            ->setStartingDate($metric->previousRange()[0])
            ->setRange(TimeFrameEnum::Custom);

        $growth = Value::make($query)
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end?->endOfDay())
            ->setStartingDate($this->start?->startOfDay())
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->count('total_net_price');

        $revenue = $metric->count();
        $previousRevenue = $previousMetric->count();

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
