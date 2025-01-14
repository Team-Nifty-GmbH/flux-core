<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WorkTimes extends MyWorkTimes
{
    public ?int $userId;

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.work-times');
    }

    public function updatedUserId(): void
    {
        $this->skipRender();
        $this->calculateByTimeFrame();
    }
}
