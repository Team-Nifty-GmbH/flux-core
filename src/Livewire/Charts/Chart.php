<?php

namespace FluxErp\Livewire\Charts;

use Livewire\Component;

class Chart extends Component
{
    public ?array $series = null;

    public ?array $annotations = null;

    public ?array $chart = null;

    public ?array $colors = null;

    public ?array $dataLabels = [
        'enabled' => false,
    ];

    public ?array $fill = [
        'opacity' => 1
    ];

    public ?array $forecastDataPoints = null;

    public ?array $grid = null;

    public ?array $labels = null;

    public ?array $legend = null;

    public ?array $markers = null;

    public ?array $noData = null;

    public ?array $plotOptions = null;

    public ?array $responsive = null;

    public ?array $states = null;

    public ?array $stroke = null;

    public ?array $theme = null;

    public ?array $title = null;

    public ?array $tooltip = null;

    public ?array $xaxis = null;

    public ?array $yaxis = null;


    public function mount(): void
    {
        $this->calculateChart();
    }

    public function calculateChart()
    {

    }

    public function getOptions(): array
    {
        $public = [];
        $reflection = new \ReflectionClass(Chart::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            $public[$property->getName()] = $this->{$property->getName()};
        }

        return array_filter($public);
    }
}
