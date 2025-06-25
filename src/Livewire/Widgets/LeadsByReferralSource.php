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

class LeadsByReferralSource extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public array $data = [];

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
            ->whereNotNull('recommended_by_address_id')
            ->whereBetween(
                'created_at',
                [
                    $this->getStart()->toDateTimeString(),
                    $this->getEnd()->toDateTimeString(),
                ]
            )
            ->groupBy('recommended_by_address_id')
            ->with('addressRecommendedBy:id,name')
            ->selectRaw('recommended_by_address_id, count(recommended_by_address_id) as total')
            ->get()
            ->map(fn ($lead) => [
                'id' => $lead->recommended_by_address_id,
                'label' => $lead->addressRecommendedBy->getLabel(),
                'total' => $lead->total,
            ])
            ->toArray();

        $this->labels = array_column($this->data, 'label');
        $this->series = array_column($this->data, 'total');
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => __('Recommended by :referrer', ['referrer' => data_get($data, 'label')]),
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
    public function show(array $address): void
    {
        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('recommended_by_address_id', data_get($address, 'id'))
                ->whereBetween('created_at', [$start, $end]),
            __('Leads recommended by :recommended-by', ['recommended-by' => data_get($address, 'label')])
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
