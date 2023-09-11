<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\Client;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Clients extends Component
{
    public array $clients;

    public int $index = -1;

    public bool $showClientModal = false;

    public bool $showClientLogosModal = false;

    protected $listeners = [
        'closeModal',
        'closeLogosModal',
    ];

    public function mount(): void
    {
        $this->clients = Client::query()
            ->with('country:id,name,iso_alpha2')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.clients');
    }

    public function show(int $index = null): void
    {
        $this->index = is_null($index) ? -1 : $index;

        if (! is_null($index)) {
            $this->dispatch('show', $this->clients[$index])->to('settings.client-edit');
        } else {
            $this->dispatch('show')->to('settings.client-edit');
        }

        $this->showClientLogosModal = false;
        $this->showClientModal = true;
        $this->skipRender();
    }

    public function showLogos(int $id): void
    {
        $this->dispatch('show', $id)->to('settings.client-logos');

        $this->showClientModal = false;
        $this->showClientLogosModal = true;
        $this->skipRender();
    }

    public function closeModal(array $client, bool $delete = false): void
    {
        $key = array_search($client['id'], array_column($this->clients, 'id'));

        if (! $delete) {
            if ($key === false) {
                $this->clients[] = $client;
            } else {
                $this->clients[$key] = $client;
            }
        } elseif ($key !== false) {
            unset($this->clients[$key]);
        }

        $this->index = 0;
        $this->showClientModal = false;
        $this->skipRender();
    }

    public function closeLogosModal(): void
    {
        $this->showClientLogosModal = false;
        $this->skipRender();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.client-edit');
    }
}
