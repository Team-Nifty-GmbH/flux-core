<?php

namespace FluxErp\Livewire\Widgets\Project;

use FluxErp\Livewire\Project\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Task;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Renderless;

class TasksByState extends CircleChart
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public ?int $projectId = null;

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
            resolve_static(Task::class, 'query')
                ->where('project_id', $this->projectId)
        )
            ->withoutRange()
            ->count('state');

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
