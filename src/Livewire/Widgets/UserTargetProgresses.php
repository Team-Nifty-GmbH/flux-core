<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class UserTargetProgresses extends MyTargetProgresses
{
    public ?int $userId = null;

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.user-target-progresses');
    }

    public function updatedUserId(): void
    {
        $this->calculateByTimeFrame();
    }
}
