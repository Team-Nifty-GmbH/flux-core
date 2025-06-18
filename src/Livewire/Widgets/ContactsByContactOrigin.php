<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Contact;
use FluxErp\Models\RecordOrigin;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
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
        $start = $this->getStart()->toDateTimeString();
        $end = $this->getEnd()->toDateTimeString();

        $origins = resolve_static(RecordOrigin::class, 'query')
            ->where('is_active', true)
            ->where('model_type', morph_alias(Contact::class))
            ->withCount([
                'contacts as value' => function ($q) use ($start, $end): void {
                    $q->whereBetween('contacts.created_at', [$start, $end]);
                },
            ])
            ->having('value', '>', 0)
            ->orderByDesc('value')
            ->get(['id', 'name']);

        $this->series = $origins->pluck('value')->all();
        $this->labels = $origins->pluck('name')->all();
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
