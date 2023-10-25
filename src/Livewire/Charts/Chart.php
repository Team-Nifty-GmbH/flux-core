<?php

namespace FluxErp\Livewire\Charts;

use FluxErp\Enums\TimeFrameEnum;
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
        'opacity' => 1,
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

    public string $timeFrame = TimeFrameEnum::LastMonth->name;

    public array $timeFrames;

    public function mount(): void
    {
        $this->timeFrames = array_map(function (TimeFrameEnum $timeFrame) {
            return [
                'value' => $timeFrame->name,
                'label' => __($timeFrame->value),
            ];
        }, TimeFrameEnum::cases());

        $this->calculateChart();
    }

    public function boot(): void
    {
        if ($this->series) {
            $this->skipRender();
        }
    }

    public function calculateChart()
    {

    }

    public function getOptions(): array
    {
        $public = [];
        $reflection = new \ReflectionClass(Chart::class);
        $properties = array_filter(
            $reflection->getProperties(\ReflectionProperty::IS_PUBLIC),
            fn ($property) => ! in_array($property->getName(), ['timeFrame'])
        );

        foreach ($properties as $property) {
            $public[$property->getName()] = method_exists($this, 'get' . strtoupper($property->getName()))
                ? $this->{'get' . strtoupper($property->getName())}()
                : $this->{$property->getName()};
        }

        return array_filter($public);
    }

    public function updatedTimeFrame(): void
    {
        $this->calculateChart();
        $this->js(
            <<<'JS'
                Alpine.$data($el.querySelector('[apex_chart]')).updateData();
            JS
        );
        $this->skipRender();
    }
}
