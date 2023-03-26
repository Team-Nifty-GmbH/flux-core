<?php

namespace FluxErp\Http\Livewire;

use Livewire\Component;

class ToggleDarkMode extends Component
{
    public bool $dark = false;

    public function mount()
    {
        $this->dark = session('dark', false);
    }

    public function updatedDark(bool $enabled): void
    {
        session()->put('dark', $enabled);
    }

    public function render()
    {
        return view('flux::livewire.toggle-dark-mode');
    }
}
