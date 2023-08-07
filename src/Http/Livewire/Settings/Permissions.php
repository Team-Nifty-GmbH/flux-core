<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\UpdateRole;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Permissions extends Component
{
    use Actions;

    public array $roles;

    public array $permissions = [];

    public string $searchPermission = '';

    public array $guards = [];

    public bool $showTogglePermissions = false;

    public bool $showToggleUsers = false;

    public ?array $selectedRole;

    public array $rolesHasPermissions;

    public array $users;

    public array $usersHasSelectedRole;

    public array $selectedUsers = [];

    public function boot(): void
    {
        $this->roles = Role::query()
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->users = User::query()
            ->where('is_active', true)
            ->get()
            ->toArray();

        $this->guards = array_keys(config('auth.guards'));
    }

    public function render(): View
    {
        return view('flux::livewire.settings.permissions');
    }

    public function updatedSearchPermission(): void
    {
        $this->getPermissions();
    }

    public function togglePermissions(Role $role): void
    {
        $this->reset('searchPermission');
        $this->selectedRole = $role->toArray();
        $this->selectedRole['permissions'] = $role->permissions->pluck('id')->toArray();
        $this->getPermissions();

        $this->showTogglePermissions = true;
    }

    public function getPermissions(): void
    {
        $query = $this->searchPermission ? Permission::search($this->searchPermission) : Permission::query();

        $this->permissions = $query
            ->where('guard_name', $this->selectedRole['guard_name'])
            ->orderBy('name')
            ->paginate(999)
            ->toArray()['data'];
    }

    public function saveTogglePermissions(): void
    {
        $action = ($this->selectedRole['id'] ?? false) ? UpdateRole::class : CreateRole::class;
        try {
            $role = $action::make($this->selectedRole)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        if ($action === CreateRole::class) {
            $this->roles[] = $role->toArray();
        }

        $this->showTogglePermissions = false;
    }

    public function toggleUsers(int $roleId): void
    {
        $this->showToggleUsers = true;
        $role = Role::query()->whereKey($roleId)->first();
        $userHasPermissionOnRole = $role->users;
        $this->selectedUsers = $userHasPermissionOnRole->pluck('id')->toArray();
        $this->selectedRole = $role->toArray();
    }

    public function saveToggleUsers(): void
    {
        $role = Role::query()->whereKey($this->selectedRole['id'])->first();
        $role->users()?->sync($this->selectedUsers);
        $this->showToggleUsers = false;
    }
}
