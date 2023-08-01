<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Projects extends Component implements UserWidget
{
    public function render(): View
    {
        return view('flux::livewire.widgets.projects');
    }

    public static function getLabel(): string
    {
        return __('Projects');
    }
}
