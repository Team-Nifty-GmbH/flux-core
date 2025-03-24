<?php

namespace FluxErp\Support\Widgets;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Renderless;
use Livewire\Component;

abstract class ValueBox extends Component
{
    use Widgetable;

    public string|float|null $growthRate = null;

    public string|float|null $previousSum = null;

    public bool $shouldBePositive = true;

    public string|float|null $subValue = null;

    public string|float $sum = 0;

    abstract public function calculateSum(): void;

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::support.widgets.value-box');
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateSum();
    }

    protected function icon(): string
    {
        return 'chart-bar';
    }

    protected function title(): ?string
    {
        return static::getLabel();
    }
}
