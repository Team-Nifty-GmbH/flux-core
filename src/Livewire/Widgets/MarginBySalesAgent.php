<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Order;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\MoneyChartFormattingTrait;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class MarginBySalesAgent extends BarChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '75%',
        ],
    ];

    public bool $showTotals = false;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    #[Renderless]
    public function calculateChart(): void
    {
        $this->series = resolve_static(Order::class, 'query')
            ->whereNotNull('agent_id')
            ->whereHas('orderType', fn (Builder $query) => $query->where('is_active', true))
            ->whereBetween('order_date', [
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString(),
            ])
            ->groupBy('agent_id')
            ->with('agent:id,name')
            ->selectRaw('agent_id, SUM(margin) as total_margin')
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->agent_id,
                'name' => $order->agent->name,
                'data' => [Rounding::round($order->total_margin)],
            ])
            ->toArray();

        $this->yaxis = [
            'labels' => ['show' => false],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'name'),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'name' => data_get($data, 'name'),
                ],
            ],
            $this->series ?? []
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('agent_id', data_get($params, 'id'))
                ->whereHas('orderType', fn (Builder $query) => $query->where('is_active', true))
                ->whereBetween('order_date', [$start, $end]),
            __('Orders by :agent-name', ['agent-name' => data_get($params, 'name')]) . ' ' .
            __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
    }
}
