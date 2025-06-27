<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Trend;
use FluxErp\Support\Metrics\Value;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Livewire\Attributes\Js;
use Livewire\Attributes\Renderless;

class AverageOrderValue extends LineChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

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

    public function calculateChart(): void
    {
        $query = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->revenue();

        $metric = Line::make($query)
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart());
        $previousMetric = Trend::make($query)
            ->setDateColumn('invoice_date')
            ->setEndingDate($this->getEndPrevious())
            ->setStartingDate($this->getStartPrevious())
            ->setRange(TimeFrameEnum::Custom);

        $growth = Value::make($query)
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->setDateColumn('invoice_date')
            ->withGrowthRate()
            ->avg('total_net_price');

        $revenue = $metric->avg('total_net_price');
        $previousRevenue = $previousMetric->avg('total_net_price');

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

    public function showTitle(): bool
    {
        return ! $this->showTotals;
    }

    #[Js]
    public function xAxisFormatter(): string
    {
        return <<<'JS'
            let name;
            if (typeof val === 'string' && val.includes('->')) {
                name = val.split('->')[1];
                val = val.split('->')[0];
            }

            return new Date(val).toLocaleDateString(document.documentElement.lang) + (name ? ' (' + name + ')' : '')
        JS;
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }
}
