<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Projects extends Component implements UserWidget
{
    public function render()
    {
        return view('flux::livewire.widgets.projects');
    }

    public static function getLabel(): string
    {
        return __('Projects');
    }
}
