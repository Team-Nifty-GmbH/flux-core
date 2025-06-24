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

class LeadsByLeadState extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

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

    #[Renderless]
    public function calculateChart(): void
    {
        $this->data = resolve_static(Lead::class, 'query')
            ->whereNotNull('lead_state_id')
            ->whereBetween(
                'created_at',
                [
                    $this->getStart()->toDateTimeString(),
                    $this->getEnd()->toDateTimeString(),
                ]
            )
            ->groupBy('lead_state_id')
            ->with('leadState:id,name,color')
            ->selectRaw('lead_state_id, count(lead_state_id) as total')
            ->get()
            ->map(fn ($lead) => [
                'id' => $lead->lead_state_id,
                'name' => $lead->leadState->name,
                'color' => $lead->leadState->color,
                'total' => $lead->total,
            ])
            ->toArray();

        $this->colors = array_column($this->data, 'color');
        $this->labels = array_column($this->data, 'name');
        $this->series = array_column($this->data, 'total');
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn ($data) => [
                'label' => __('Leads with state :lead-state', ['lead-state' => data_get($data, 'name')]),
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
    public function show(array $leadState): void
    {
        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('lead_state_id', data_get($leadState, 'id'))
                ->whereBetween(
                    'created_at',
                    [
                        $this->getStart()->toDateTimeString(),
                        $this->getEnd()->toDateTimeString(),
                    ]
                ),
            __('Leads with state :lead-state', ['lead-state' => data_get($leadState, 'name')])
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
