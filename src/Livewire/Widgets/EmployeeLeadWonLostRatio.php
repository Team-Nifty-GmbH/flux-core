<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class EmployeeLeadWonLostRatio extends MyLeadWonLostRatio
{
    public bool $showDonutOptions = false;

    public ?int $userId = null;

    public function render(): View|Factory
    {
        return view('flux::livewire.widgets.employee-lead-won-lost-ratio');
    }

    public function updatedUserId(): void
    {
        $this->skipRender();
        $this->calculateByTimeFrame();
    }
}
