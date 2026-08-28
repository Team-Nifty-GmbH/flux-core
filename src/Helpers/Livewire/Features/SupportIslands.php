<?php

namespace FluxErp\Helpers\Livewire\Features;

use Closure;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportIslands extends ComponentHook
{
    public function renderIsland($name, $view, $data): Closure
    {
        $revert = Utils::shareWithViews('__livewire', $this->component);

        return fn () => $revert();
    }
}
