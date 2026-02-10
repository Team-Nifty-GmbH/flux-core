<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Lead;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class LeadsByRecordOrigin extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public array $data = [];

    public bool $showTotals = false;

    public static function getCategory(): ?string
    {
        return 'Leads';
    }

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
            ->whereNotNull('record_origin_id')
            ->whereBetween('created_at', [
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString(),
            ])
            ->groupBy('record_origin_id')
            ->with('recordOrigin:id,name,is_active')
            ->selectRaw('record_origin_id, COUNT(id) as total')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn (Model $lead) => $lead->recordOrigin?->is_active !== false
                ? [
                    'id' => $lead->record_origin_id,
                    'label' => $lead->recordOrigin?->name ?? __('Unassigned Leads'),
                    'total' => $lead->total,
                ] : null,
            )
            ->filter()
            ->toArray();

        $this->labels = array_column($this->data, 'label');
        $this->series = array_column($this->data, 'total');
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
    public function show(array $recordOrigin): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('record_origin_id', data_get($recordOrigin, 'id'))
                ->whereBetween('created_at', [$start, $end]),
            __('Leads by :record-origin', ['record-origin' => data_get($recordOrigin, 'label')]),
        )->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
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
