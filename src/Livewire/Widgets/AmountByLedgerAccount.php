<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\OrderPosition;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class AmountByLedgerAccount extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

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
            $tempLabels[$indexOfEmptyLabel] = __('Not Assigned');
        }

        $this->labels = $tempLabels;
        $this->series = $metrics->getData();
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

    public function options(): array
    {
        return collect($this->labels)
            ->map(fn ($label) => [
                'label' => $label,
                'method' => 'show',
                'params' => $label,
            ])
            ->toArray();
    }

    #[Renderless]
    public function show(string $ledgerAccountLabel): void
    {
        $startCarbon = $this->getStart();
        $endCarbon = $this->getEnd();

        $start = $startCarbon->toDateString();
        $end = $endCarbon->toDateString();

        $localizedStart = $startCarbon->translatedFormat('j. F Y');
        $localizedEnd = $endCarbon->translatedFormat('j. F Y');

        $ledgerAccountFilterLabel = $ledgerAccountLabel === __('Not Assigned') ? null : $ledgerAccountLabel;

        SessionFilter::make(
            Livewire::new(resolve_static(OrderPositionList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('total_gross_price')
                ->whereBetween('created_at', [
                    $start,
                    $end,
                ])
                ->where('ledger_account_id', $ledgerAccountFilterLabel),
            __('Amount held in :ledger-account', ['ledger-account' => $ledgerAccountLabel]) . ' ' .
            __('between :start and :end', ['start' => $localizedStart, 'end' => $localizedEnd]),
        )
            ->store();

        $this->redirectRoute('orders.order-positions', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
