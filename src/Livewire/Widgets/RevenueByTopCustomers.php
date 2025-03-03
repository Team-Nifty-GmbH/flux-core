<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Support\Widgets\Charts\CircleChart;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Livewire\Attributes\Renderless;

class RevenueByTopCustomers extends CircleChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public bool $showTotals = false;

    public int $limit = 10;

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateByTimeFrame',
        ];
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
            ->setEndingDate($this->end?->endOfDay())
            ->setStartingDate($this->start?->startOfDay())
            ->setLabelKey('addressInvoice.name')
            ->sum('total_net_price', 'address_invoice_id');

        $this->series = $metrics->getData();
        $this->labels = $metrics->getLabels();
    }
}
