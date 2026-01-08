<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Support\Metrics\Value;
use FluxErp\Traits\Livewire\Widget\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\MoneyChartFormattingTrait;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class AverageOrderValue extends LineChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

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

        $previousMetric = Line::make($query)
            ->setDateColumn('invoice_date')
            ->setEndingDate($this->getEndPrevious())
            ->setStartingDate($this->getStartPrevious())
            ->setRange($this->timeFrame);

        $growth = Value::make($query)
            ->setDateColumn('invoice_date')
            ->setStartingDate($this->getStart())
            ->setEndingDate($this->getEnd())
            ->setPreviousStartingDate($this->getStartPrevious())
            ->setPreviousEndingDate($this->getEndPrevious())
            ->setRange(TimeFrameEnum::Custom)
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

    #[Renderless]
    public function options(): array
    {
        return [
            [
                'label' => static::getLabel(),
                'method' => 'redirectPeriod',
                'params' => 'current',
            ],
            [
                'label' => __('Previous Period'),
                'method' => 'redirectPeriod',
                'params' => 'previous',
            ],
        ];
    }

    #[Renderless]
    public function redirectPeriod(string $period): void
    {
        if (strtolower($period) === 'previous') {
            $start = $this->getStartPrevious()->toDateString();
            $end = $this->getEndPrevious()->toDateString();
        } else {
            $start = $this->getStart()->toDateString();
            $end = $this->getEnd()->toDateString();
        }
        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->revenue()
                ->whereBetween('invoice_date', [$start, $end]),
            static::getLabel() . ' ' . __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }
}
