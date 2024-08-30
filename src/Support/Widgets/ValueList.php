<?php

namespace FluxErp\Support\Widgets;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

abstract class ValueList extends Component
{
    use Widgetable;

    public bool $shouldBePositive = true;

    public array $items = [];

    abstract public function calculateList(): void;

    public function mount(): void
    {
        $this->calculateList();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::support.widgets.value-list');
    }

    public function updatedTimeFrame(): void
    {
        $this->calculateList();
    }

    public function calculateByTimeFrame(): void
    {
        $this->calculateList();
    }

    protected function title(): ?string
    {
        return static::getLabel();
    }
}
