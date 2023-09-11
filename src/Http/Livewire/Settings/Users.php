<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class Users extends Component
{
    use WithPagination, Actions;

    public string $search = '';

    public bool $showUserModal = false;

    public int $userId = 0;

    protected $listeners = ['closeModal'];

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.users');
    }

    public function show(int $id = null): void
    {
        $this->userId = $id ?? 0;
        $this->dispatch('show', $id)->to('settings.user-edit');
        $this->skipRender();
        $this->showUserModal = true;
    }

    public function closeModal(): void
    {
        User::query()
            ->whereKey($this->userId)
            ->first()
            ?->lock()
            ->delete();

        $this->reset();
    }

    public function delete(): void
    {
        $this->dispatch('delete')->to('settings.user-edit');
    }
}
