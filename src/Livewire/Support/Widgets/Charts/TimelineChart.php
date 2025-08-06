<?php

namespace FluxErp\Livewire\Support\Widgets\Charts;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Js;

abstract class TimelineChart extends Chart
{
    public ?array $chart = [
        'type' => 'rangeBar',
    ];

    public bool $showTotals = false;

    public function render(): View|Factory
    {
        return view('flux::livewire.support.widgets.charts.bar-chart');
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.vertical-bar');
    }

    #[Js]
    public function xAxisFormatter(): string
    {
        return <<<'JS'
            return new Date(val).toLocaleDateString(document.documentElement.lang);
        JS;
    }
}
