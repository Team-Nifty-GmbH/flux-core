<?php

use FluxErp\Livewire\Widgets\MarginBySalesAgent;
use FluxErp\Livewire\Widgets\OrdersByTypeChart;
use FluxErp\Livewire\Widgets\RevenueBySalesRepresentative;
use Livewire\Livewire;

test('chart options only carry apex chart keys', function (string $widget): void {
    Livewire::test($widget)
        ->assertSet(
            'options',
            fn (array $options): bool => array_diff(
                array_keys($options),
                [
                    'annotations', 'chart', 'colors', 'dataLabels', 'fill', 'forecastDataPoints',
                    'grid', 'labels', 'legend', 'markers', 'noData', 'plotOptions', 'responsive',
                    'series', 'states', 'stroke', 'theme', 'title', 'tooltip', 'xaxis', 'yaxis',
                ]
            ) === []
        );
})->with([
    MarginBySalesAgent::class,
    OrdersByTypeChart::class,
    RevenueBySalesRepresentative::class,
]);
