<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Contact;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Support\Widgets\Charts\CircleChart;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Livewire\Attributes\Renderless;

class ContactsByContactOrigin extends CircleChart
{
    use IsTimeFrameAwareWidget;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public bool $showTotals = false;

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
            resolve_static(Contact::class, 'query')
                ->whereNotNull('contact_origin_id')
                ->join('contact_origins', 'contact_origins.id', '=', 'contacts.contact_origin_id')
                ->where('contact_origins.is_active', true)
                ->orderByRaw('COUNT(*) DESC')
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setLabelKey('contactOrigin.name')
            ->count('contact_origin_id');

        $this->series = $metrics->getData();
        $this->labels = $metrics->getLabels();
    }
}
