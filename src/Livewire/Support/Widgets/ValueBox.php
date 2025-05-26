<?php

namespace FluxErp\Livewire\Support\Widgets;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
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

    public function render(): View
    {
        return view('flux::livewire.support.widgets.value-box');
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
