<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Order;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class RevenueByTopCustomers extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public array $data = [];

    public int $limit = 10;

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

    public function calculateChart(): void
    {
        $this->data = resolve_static(Order::class, 'query')
            ->revenue()
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereBetween(
                'invoice_date',
                [
                    $this->getStart()->toDateString(),
                    $this->getEnd()->toDateString(),
                ]
            )
            ->groupBy('address_invoice_id')
            ->selectRaw('address_invoice_id, SUM(total_net_price) as total')
            ->orderBy('total', 'desc')
            ->with('addressInvoice:id,name')
            ->limit($this->limit)
            ->get()
            ->map(fn (Model $order) => [
                'id' => $order->address_invoice_id,
                'label' => $order->addressInvoice?->getLabel() ?? __('Unknown'),
                'total' => $order->total,
            ])
            ->toArray();

        $this->labels = array_column($this->data, 'label');
        $this->series = array_column($this->data, 'total');
    }

    public function getPlotOptions(): array
    {
        return [
            'pie' => [
                'donut' => [
                    'labels' => [
                        'show' => true,
                        'total' => [
                            'show' => true,
                            'label' => __('Total'),
                        ],
                    ],
                ],
            ],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => __('Orders by :agent-name', ['agent-name' => data_get($data, 'label')]),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'label' => data_get($data, 'label'),
                ],
            ],
            $this->data
        );
    }

    #[Renderless]
    public function show(array $invoiceAddress): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('address_invoice_id', data_get($invoiceAddress, 'id'))
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereBetween('invoice_date', [
                    $start,
                    $end,
                ])
                ->revenue(),
            __('Orders by :agent-name', ['agent-name' => data_get($invoiceAddress, 'label')]) . ' '
            . __('between :start and :end', ['start' => $start, 'end' => $end]),
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
