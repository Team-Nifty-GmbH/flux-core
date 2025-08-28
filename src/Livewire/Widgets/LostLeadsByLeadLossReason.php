<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Lead;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class LostLeadsByLeadLossReason extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public array $data = [];

    public bool $showTotals = true;

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

    #[Renderless]
    public function calculateChart(): void
    {
        $this->data = resolve_static(Lead::class, 'query')
            ->whereNotNull('lead_loss_reason_id')
            ->whereHas('leadLossReason', fn (Builder $query) => $query->where('is_active', true))
            ->whereBetween(
                'created_at',
                [
                    $this->getStart()->toDateTimeString(),
                    $this->getEnd()->toDateTimeString(),
                ]
            )
            ->groupBy('lead_loss_reason_id')
            ->with('leadLossReason:id,name')
            ->selectRaw('lead_loss_reason_id, count(lead_loss_reason_id) as total')
            ->get()
            ->map(fn ($lead) => [
                'id' => $lead->lead_loss_reason_id,
                'name' => $lead->leadLossReason->name,
                'total' => $lead->total,
            ])
            ->toArray();

        $this->labels = array_column($this->data, 'name');
        $this->series = array_column($this->data, 'total');
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => __('Leads with lead loss reason: :lead-loss-reason', [
                    'lead-loss-reason' => data_get($data, 'name'),
                ]),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'name' => data_get($data, 'name'),
                ],
            ],
            $this->data
        );
    }

    #[Renderless]
    public function show(array $leadLossReason): void
    {
        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('lead_loss_reason_id', data_get($leadLossReason, 'id'))
                ->whereBetween('created_at', [$start, $end]),
            __(
                'Leads with lead loss reason: :lead-loss-reason',
                [
                    'lead-loss-reason' => data_get($leadLossReason, 'name'),
                ]
            ),
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }

    protected function getPlotOptions(): array
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
}
