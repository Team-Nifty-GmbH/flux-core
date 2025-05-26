<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Value;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Renderless;

class TotalRevenue extends LineChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
    }

    #[Renderless]
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
            ->sum('total_net_price');

        $revenue = $metric->sum('total_net_price');
        $previousRevenue = $previousMetric->sum('total_net_price');

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

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }
}
