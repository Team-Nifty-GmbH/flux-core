<?php

namespace FluxErp\Livewire\Portal;

use Livewire\Component;

class Dashboard extends Component
{
    public ?string $view = null;

    public function boot(): void
    {
        $client = auth()->user()?->contact?->client;
        $setting = $client?->settings()->where('key', 'customerPortal')->first()?->toArray();

        $this->view = $setting['settings']['dashboard_module'] ?? null;
    }

    public function render()
    {
        return view('flux::livewire.portal.dashboard');
    }
}
