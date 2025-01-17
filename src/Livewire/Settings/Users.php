<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use Actions, WithPagination;

    public string $search = '';

    public bool $showUserModal = false;

    public int $userId = 0;

    protected $listeners = [
        'closeModal',
    ];

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.users');
    }

    public function show(?int $id = null): void
    {
        if ($this->showUserModal) {
            return;
        }

        $this->userId = $id ?? 0;
        $this->dispatch('show', $id)->to('settings.user-edit');
        $this->showUserModal = true;
    }

    public function closeModal(): void
    {
        $this->reset();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.user-edit');
    }
}
