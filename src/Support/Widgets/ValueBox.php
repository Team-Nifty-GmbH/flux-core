<?php

namespace FluxErp\Support\Widgets;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

abstract class ValueBox extends Component
{
    use Widgetable;

    public float|string $sum = 0;

    public float|string|null $previousSum = null;

    public ?float $growthRate = null;

    public bool $shouldBePositive = true;

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::support.widgets.value-box');
    }

    public function updatedTimeFrame(): void
    {
        $this->calculateSum();
    }

    abstract public function calculateSum(): void;

    protected function icon(): string
    {
        return 'chart-bar';
    }

    protected function title(): ?string
    {
        return static::getLabel();
    }
}
