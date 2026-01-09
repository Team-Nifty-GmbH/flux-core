<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\TimelineChart;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

class OrdersTimeline extends TimelineChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    #[Locked]
    public array $data = [];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'rangeBarGroupRows' => true,
        ],
    ];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultHeight(): int
    {
        return 3;
    }

    public static function getDefaultWidth(): int
    {
        return 4;
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
        $orders = resolve_static(Order::class, 'query')
            ->whereNotNull('system_delivery_date')
            ->whereHas('orderType', fn (Builder $query) => $query->where('is_active', true))
            ->whereBetween('system_delivery_date', [
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString(),
            ])
            ->with('orderType:id,name')
            ->select(['id', 'order_number', 'order_type_id', 'system_delivery_date', 'system_delivery_date_end'])
            ->orderBy('system_delivery_date')
            ->get();

        $this->data = $orders
            ->map(fn (Order $order): array => [
                'id' => $order->getKey(),
                'order_number' => $order->order_number,
                'order_type' => $order->orderType?->name,
            ])
            ->toArray();

        $this->series = [
            [
                'data' => $orders
                    ->map(fn (Order $order): array => [
                        'x' => __('Order') . ' #' . $order->order_number,
                        'y' => [
                            $order->system_delivery_date->timestamp * 1000,
                            ($order->system_delivery_date_end ?? $order->system_delivery_date)->timestamp * 1000,
                        ],
                        'fillColor' => '#3b82f6',
                    ])
                    ->toArray(),
            ],
        ];

        $this->xaxis = [
            'type' => 'datetime',
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => __('Order') . ' #' . data_get($data, 'order_number'),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                ],
            ],
            $this->data
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $this->redirectRoute('orders.id', ['id' => data_get($params, 'id')], navigate: true);
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
