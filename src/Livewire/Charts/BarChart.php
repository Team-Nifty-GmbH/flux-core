<?php

namespace FluxErp\Livewire\Charts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

abstract class BarChart extends Chart
{
    public ?array $chart = [
        'type' => 'bar',
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => false,
            'endingShape' => 'rounded',
            'columnWidth' => '75%',
        ],
    ];

    public ?array $stroke = [
        'show' => true,
        'width' => 2,
        'colors' => ['transparent'],
    ];

    public bool $showTotals = true;

    public function render(): View|Factory
    {
        return view('flux::livewire.charts.bar-chart');
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.vertical-bar');
    }
}
