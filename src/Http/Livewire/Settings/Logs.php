<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\Log;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Logs extends Component
{
    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.logs');
    }

    public function loadLog(Log $log): array
    {
        return $log->toArray();
    }
}
