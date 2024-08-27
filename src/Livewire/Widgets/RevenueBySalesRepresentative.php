<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Order;
use FluxErp\Support\Metrics\Charts\Doughnut;
use FluxErp\Support\Widgets\Charts\CircleChart;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;

class RevenueBySalesRepresentative extends CircleChart
{
    use MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public bool $showTotals = false;

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

    public function calculateChart(): void
    {
        $metrics = Doughnut::make(
            resolve_static(Order::class, 'query')
                ->revenue()
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereNotNull('agent_id')
                ->with('agent:id,name')
        )
            ->dateColumn('invoice_date')
            ->range($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setLabelKey('agent.name')
            ->sum('total_net_price', 'agent_id');

        $this->series = $metrics->getData();
        $this->labels = $metrics->getLabels();
    }
}
