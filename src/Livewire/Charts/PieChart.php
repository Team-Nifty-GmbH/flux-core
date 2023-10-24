<?php

namespace FluxErp\Livewire\Charts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PieChart extends Chart
{
    public ?array $chart = [
        'type' => 'pie',
    ];

    public bool $showTotals = true;

    public function render(): View|Factory
    {
        return view('flux::livewire.charts.bar-chart');
    }
}
