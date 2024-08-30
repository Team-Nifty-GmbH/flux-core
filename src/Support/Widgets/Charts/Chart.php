<?php

namespace FluxErp\Support\Widgets\Charts;

use Livewire\Attributes\Renderless;
use Livewire\Component;

abstract class Chart extends Component
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

    public array $chartTypes = [];

    public ?array $options = null;

    abstract public function calculateChart(): void;

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->calculateChart();
        $this->options = $this->getOptions();
    }

    public function boot(): void
    {
        if ($this->series) {
            $this->skipRender();
        }
    }

    public function rendering(): void
    {
        $this->options ??= $this->getOptions();
    }

    #[Renderless]
    public function updateData(): void
    {
        $this->options = $this->getOptions();
        $this->js(
            <<<'JS'
                Alpine.$data($el).updateData();
            JS
        );
    }

    protected function getOptions(): array
    {
        $public = [];
        $reflection = new \ReflectionClass(Chart::class);
        $properties = array_filter(
            $reflection->getProperties(\ReflectionProperty::IS_PUBLIC),
            fn ($property) => ! in_array($property->getName(), ['timeFrame', 'options'])
        );

        foreach ($properties as $property) {
            $public[$property->getName()] = method_exists($this, 'get' . strtoupper($property->getName()))
                ? $this->{'get' . strtoupper($property->getName())}()
                : $this->{$property->getName()};
        }

        return array_filter($public);
    }
}
