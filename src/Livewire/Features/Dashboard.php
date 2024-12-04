<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\RendersWidgets;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Dashboard extends Component
{
    use Actions, RendersWidgets;

    #[Locked]
    public ?int $dashboardId = null;

    protected string $component;

    public function render(): View
    {
        return view('flux::livewire.features.dashboard');
    }
}
