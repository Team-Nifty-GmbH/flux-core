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

    public function mount(): void
    {
        $this->calculateSum();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::support.widgets.value-list');
    }

    public function updatedTimeFrame(): void
    {
        $this->timeFrameChanged($this->timeFrame);
    }

    abstract public function calculateSum(): void;

    protected function title(): ?string
    {
        return static::getLabel();
    }
}
