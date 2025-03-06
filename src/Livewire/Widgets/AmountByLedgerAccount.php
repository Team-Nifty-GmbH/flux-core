<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\OrderPosition;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Support\Widgets\Charts\CircleChart;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use Livewire\Attributes\Renderless;

class AmountByLedgerAccount extends CircleChart
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public bool $showTotals = false;

    public int $limit = 10;

    public function showTitle(): bool
    {
        return true;
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
            resolve_static(OrderPosition::class, 'query')
                ->whereNotNull('total_gross_price')
                ->with('ledgerAccount:id,name')
                ->orderBy('result', 'desc')
        )
            ->setDateColumn('created_at')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setLabelKey('ledgerAccount.name')
            ->sum('total_gross_price', 'ledger_account_id');


        $tempLabels = $metrics->getLabels();
        $indexOfEmptyLabel = array_search('', $tempLabels);

        if ($indexOfEmptyLabel !== false) {
            $tempLabels[$indexOfEmptyLabel] =  __('Not Assigned');
        }

        $this->labels = $tempLabels;
        $this->series = $metrics->getData();

    }
}
