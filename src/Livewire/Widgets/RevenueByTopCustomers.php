<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class RevenueByTopCustomers extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'donut',
    ];

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
        $metrics = Donut::make(
            resolve_static(Order::class, 'query')
                ->revenue()
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->limit($this->limit)
                ->with('addressInvoice:id,name')
                ->orderBy('result', 'desc')
        )
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->setLabelKey('addressInvoice.name')
            ->sum('total_net_price', 'address_invoice_id');

        $this->series = $metrics->getData();
        $this->labels = $metrics->getLabels();
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
        return collect($this->labels)
            ->map(fn ($label) => [
                'label' => __('Orders by :agent-name', ['agent-name' => $label]),
                'method' => 'show',
                'params' => $label,
            ])
            ->toArray();
    }

    #[Renderless]
    public function show(string $clientName): void
    {
        $startCarbon = $this->getStart();
        $endCarbon = $this->getEnd();

        $start = $startCarbon->toDateString();
        $end = $endCarbon->toDateString();

        $localizedStart = $startCarbon->translatedFormat('j. F Y');
        $localizedEnd = $endCarbon->translatedFormat('j. F Y');

        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereBetween('invoice_date', [
                    $start,
                    $end,
                ])
                ->revenue()
                ->join('addresses', 'orders.address_invoice_id', '=', 'addresses.id')
                ->where('addresses.name', $clientName),
            __('Orders by :agent-name', ['agent-name' => $clientName]) . ' ' .
            __('between :start and :end', ['start' => $localizedStart, 'end' => $localizedEnd]),
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
