<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Support\Metrics\Charts\Line;
use FluxErp\Traits\Livewire\HasTemporalXAxisFormatter;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\SupportsWidgetConfig;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class OrdersByTypeChart extends LineChart implements HasWidgetOptions
{
    use HasTemporalXAxisFormatter, IsTimeFrameAwareWidget, SupportsWidgetConfig, Widgetable;

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

    public function calculateChart(): void
    {
        $orderTypes = resolve_static(OrderType::class, 'query')
            ->where('is_active', true)
            ->get('id');

        $this->series = [];
        $metrics = [];
        $colorIndex = 0;

        foreach ($orderTypes as $orderType) {
            $query = resolve_static(Order::class, 'query')
                ->where('order_type_id', $orderType->getKey());

            $metric = Line::make($query)
                ->setDateColumn('created_at')
                ->setRange($this->timeFrame)
                ->setEndingDate($this->getEnd()->endOfDay())
                ->setStartingDate($this->getStart()->startOfDay())
                ->count();

            $metrics[] = [
                'metric' => $metric,
                'orderType' => $orderType,
                'colorIndex' => $colorIndex++,
            ];
        }

        if (! $metrics) {
            $this->series = [];
            $this->xaxis['categories'] = [];

            return;
        }

        $this->series = collect($metrics)
            ->map(fn (array $data): array => [
                'name' => data_get($data, 'orderType.name'),
                'color' => ChartColorEnum::forIndex(data_get($data, 'colorIndex'))->value,
                'data' => data_get($data, 'metric')->getData(),
                'orderTypeId' => data_get($data, 'orderType')->getKey(),
                'hidden' => data_get(
                    $this->config,
                    'series.' . data_get($data, 'orderType')->getKey() . '.hidden',
                    false
                ),
            ])
            ->toArray();

        $this->xaxis['categories'] = data_get($metrics, '0.metric')->getLabels();
    }

    #[Renderless]
    public function options(): array
    {
        $options = [];
        foreach ($this->series as $series) {
            if (data_get($series, 'orderTypeId')) {
                $options[] = [
                    'label' => data_get($series, 'name'),
                    'method' => 'showByOrderType',
                    'params' => data_get($series, 'orderTypeId'),
                ];
            }
        }

        return $options;
    }

    #[Renderless]
    public function showByOrderType(int $orderTypeId): void
    {
        $orderType = resolve_static(OrderType::class, 'query')
            ->where('id', $orderTypeId)
            ->first([
                'id',
                'name',
            ]);

        if (! $orderType) {
            return;
        }

        $start = $this->getStart()->startOfDay();
        $end = $this->getEnd()->endOfDay();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('order_type_id', $orderTypeId)
                ->whereBetween('created_at', [$start, $end]),
            $orderType->name . ' ' . __(
                'between :start and :end',
                [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ]
            ),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    #[On('apex-legend-click')]
    #[Renderless]
    public function toggleSeries(string $type, array $payload): void
    {
        if ($orderTypeId = data_get($this->series, data_get($payload, 'seriesIndex') . '.orderTypeId')) {
            $this->storeConfig(
                'series.' . $orderTypeId . '.hidden',
                ! data_get($payload, 'isHidden', true)
            );
        }
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderCreated' => 'calculateByTimeFrame',
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderUpdated' => 'calculateByTimeFrame',
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderDeleted' => 'calculateByTimeFrame',
        ];
    }
}
