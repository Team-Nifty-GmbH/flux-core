<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;

class UserTargetProgresses extends MyTargetProgresses
{
    public ?int $userId = null;

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.user-target-progresses');
    }

    #[Renderless]
    public function updatedUserId(): void
    {
        $this->calculateByTimeFrame();
    }
}
