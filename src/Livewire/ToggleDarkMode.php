<?php

namespace FluxErp\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ToggleDarkMode extends Component
{
    public bool $dark = false;

    public function mount(): void
    {
        $this->dark = session('dark', false);
    }

    public function render(): Factory|Application|View
    {
        return view('flux::livewire.toggle-dark-mode');
    }

    public function updatedDark(bool $enabled): void
    {
        session()->put('dark', $enabled);

        auth()->user()?->update(['is_dark_mode' => $enabled]);
    }
}
