<?php

namespace FluxErp\Support\Widgets\Charts;

abstract class LineChart extends BarChart
{
    public ?array $chart = [
        'type' => 'line',
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
        'width' => 4,
        'curve' => 'smooth',
        'colors' => ['transparent'],
    ];
}
