<?php

namespace FluxErp\Livewire\Charts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class DonutChart extends Chart
{
    public ?array $chart = [
        'type' => 'donut',
    ];

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

    public bool $showTotals = true;

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.donut');
    }

    public function render(): View|Factory
    {
        return view('flux::livewire.charts.donut-chart');
    }
}
