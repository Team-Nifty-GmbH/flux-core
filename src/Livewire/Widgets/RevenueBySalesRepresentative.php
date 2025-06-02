<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Livewire\Attributes\Renderless;

class RevenueBySalesRepresentative extends CircleChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'donut',
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

    public function calculateChart(): void
    {
        $metrics = Donut::make(
            resolve_static(Order::class, 'query')
                ->revenue()
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereNotNull('agent_id')
                ->with('agent:id,name')
        )
            ->setDateColumn('invoice_date')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->setLabelKey('agent.name')
            ->sum('total_net_price', 'agent_id');

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
