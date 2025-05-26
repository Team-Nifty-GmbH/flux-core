<?php

namespace FluxErp\Livewire\Support\Widgets\Charts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

abstract class CircleChart extends Chart
{
    public ?array $chart = [
        'type' => 'pie',
    ];

    public bool $showTotals = true;

    public function render(): View|Factory
    {
        return view('flux::livewire.support.widgets.charts.circle-chart');
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.circle');
    }
}
