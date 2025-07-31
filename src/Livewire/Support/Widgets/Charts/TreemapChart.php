<?php

namespace FluxErp\Livewire\Support\Widgets\Charts;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Js;

abstract class TreemapChart extends Chart
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'treemap',
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    public ?array $plotOptions = [
        'treemap' => [
            'enableShades' => true,
            'shadeIntensity' => 0.5,
            'reverseNegativeShade' => true,
            'useFillColorAsStroke' => true,
        ],
    ];

    public bool $showTotals = false;

    public ?array $stroke = [
        'show' => true,
        'width' => 2,
        'colors' => ['transparent'],
    ];

    public function render(): View|Factory
    {
        return view('flux::livewire.support.widgets.charts.chart');
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return <<<'JS'
            return opts.w.config.xaxis.categories[opts.dataPointIndex] + ': ' + val;
    JS;
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.vertical-bar');
    }
}
