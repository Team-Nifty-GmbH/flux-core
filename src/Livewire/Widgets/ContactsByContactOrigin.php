<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Contact;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Renderless;

class ContactsByContactOrigin extends CircleChart
{
    use IsTimeFrameAwareWidget, Widgetable;

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
        $query = resolve_static(Contact::class, 'query')
            ->whereNotNull('origin_id')
            ->join('record_origins', 'record_origins.id', '=', 'contacts.origin_id')
            ->where('record_origins.is_active', true)
            ->where('record_origins.model_type', morph_alias(Contact::class))
            ->select('record_origins.id', 'record_origins.name', DB::raw('COUNT(*) AS value'))
            ->groupBy('record_origins.id', 'record_origins.name')
            ->orderBy('value', 'desc');

        $metrics = Donut::make($query)
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->setLabelKey('name')
            ->count('record_origins.id', 'value');

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
}
