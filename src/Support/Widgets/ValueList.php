<?php

namespace FluxErp\Support\Widgets;

use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Renderless;
use Livewire\Component;

abstract class ValueList extends Component
{
    use Widgetable;

    public bool $shouldBePositive = true;

    public array $items = [];

    public int $limit = 10;

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

    public function showMore(): void {}

    #[Renderless]
    public function hasMore(): bool
    {
        return false;
    }

    public function calculateByTimeFrame(): void
    {
        $this->calculateList();
    }

    protected function title(): ?string
    {
        return static::getLabel();
    }

    protected function hasLoadMore(): bool
    {
        return false;
    }
}
