<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use Livewire\Component;

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
