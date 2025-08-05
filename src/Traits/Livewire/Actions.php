<?php

namespace FluxErp\Traits\Livewire;

use TallStackUi\Foundation\Interactions\Toast;
use TallStackUi\Traits\Interactions as BaseActions;

trait Actions
{
    use BaseActions;

    public function notification(): Toast
    {
        return $this->toast();
    }
}
