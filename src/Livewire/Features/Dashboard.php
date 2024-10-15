<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Traits\RendersWidgets;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use WireUi\Traits\Actions;

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
