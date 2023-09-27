<?php

namespace FluxErp\Livewire\Settings;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

class DiscountGroups extends Component
{
    public function render(): Factory|Application|View
    {
        return view('flux::livewire.settings.discount-groups');
    }
}
