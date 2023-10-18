<?php

namespace FluxErp\Livewire\Settings;

use Livewire\Component;

class FormBuilder extends Component
{
    public function render()
    {
        return view('flux::livewire.settings.form-builder');
    }

    public function editItem($id = null)
    {
        dd($id);
    }
}
