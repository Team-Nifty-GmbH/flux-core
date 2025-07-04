<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\OrderPosition;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class AmountByLedgerAccount extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public array $data = [];

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
        $this->data = resolve_static(OrderPosition::class, 'query')
            ->whereNotNull('total_gross_price')
            ->whereBetween(
                'created_at',
                [
                    $this->getStart()->toDateTimeString(),
                    $this->getEnd()->toDateTimeString(),
                ]
            )
            ->groupBy('ledger_account_id')
            ->with('ledgerAccount:id,name')
            ->selectRaw('ledger_account_id, SUM(total_gross_price) as total')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn (Model $orderPosition) => [
                'id' => $orderPosition->ledger_account_id,
                'label' => $orderPosition->ledgerAccount?->name ?? __('Not Assigned'),
                'total' => $orderPosition->total,
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
                'label' => data_get($data, 'label'),
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
    public function show(array $ledgerAccount): void
    {
        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(OrderPositionList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('total_gross_price')
                ->whereBetween('created_at', [
                    $start,
                    $end,
                ])
                ->where('ledger_account_id', data_get($ledgerAccount, 'id')),
            __(
                'Amount held in Ledger Account :ledger-account',
                [
                    'ledger-account' => data_get($ledgerAccount, 'label'),
                ]
            ) . ' '
            . __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('orders.order-positions', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
