<?php

namespace FluxErp\Livewire\Support\Widgets\Charts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

abstract class RadialBarChart extends Chart
{
    public ?array $chart = [
        'type' => 'radialBar',
    ];

    public ?int $max = null;

    public function render(): View|Factory
    {
        return view('flux::livewire.support.widgets.charts.radial-bar-chart');
    }

    public function getPlotOptions(): array
    {
        return [
            'radialBar' => [
                'dataLabels' => [
                    'total' => [
                        'show' => true,
                        'label' => __('Total'),
                    ],
                ],
            ],
        ];
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.circle');
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        if (isset($options['series'])) {
            $max = $this->max ?? max($options['series']);

            if ($max > 0) {
                $options['series'] = array_map(
                    fn ($value) => ($value / $max) * 100,
                    $options['series']
                );
            }
        }

        return $options;
    }
}
