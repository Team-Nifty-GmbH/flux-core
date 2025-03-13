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

    public array $items = [];

    public int $limit = 10;

    public bool $shouldBePositive = true;

    abstract public function calculateList(): void;

    public function mount(): void
    {
        $this->calculateList();
    }

    public function render(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('flux::support.widgets.value-list');
    }

    public function calculateByTimeFrame(): void
    {
        $this->calculateList();
    }

    #[Renderless]
    public function hasMore(): bool
    {
        return false;
    }

    public function showMore(): void {}

    public function updatedTimeFrame(): void
    {
        $this->calculateList();
    }

    protected function hasLoadMore(): bool
    {
        return false;
    }

    protected function title(): ?string
    {
        return static::getLabel();
    }
}
