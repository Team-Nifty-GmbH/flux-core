<?php

namespace FluxErp\Livewire\Support\Widgets\Charts;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Js;

abstract class FunnelChart extends Chart
{
    use Widgetable;

    public ?array $chart = [
        'type' => 'bar',
        'dropShadow' => [
            'enabled' => true,
        ],
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'borderRadius' => 0,
            'horizontal' => true,
            'barHeight' => '80%',
            'isFunnel' => true,
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
        return view('flux::livewire.support.widgets.charts.bar-chart');
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
        return view('flux::livewire.placeholders.circle');
    }
}
