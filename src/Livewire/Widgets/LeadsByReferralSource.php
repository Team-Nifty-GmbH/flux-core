<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Lead;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class LeadsByReferralSource extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

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
        $metrics = Donut::make(
            resolve_static(Lead::class, 'query')
                ->whereNotNull('recommended_by_address_id')
                ->with('addressRecommendedBy:id,name')
        )
            ->setDateColumn('created_at')
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->setLabelKey('addressRecommendedBy.name')
            ->count('recommended_by_address_id', 'id');

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

    #[Renderless]
    public function options(): array
    {
        return collect($this->labels)
            ->map(fn ($label) => [
                'label' => __('Recommended by :referrer', ['referrer' => $label]),
                'method' => 'show',
                'params' => $label,
            ])
            ->toArray();
    }

    #[Renderless]
    public function show(string $addressName): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('recommended_by_address_id')
                ->whereRelation('addressRecommendedBy', 'name', $addressName),
            __('Leads recommended by :referralSourceName', ['referralSourceName' => $addressName])
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }
}
