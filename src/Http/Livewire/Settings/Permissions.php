<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Http\Requests\CreateRoleRequest;
use FluxErp\Http\Requests\UpdateRoleRequest;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Services\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

class Permissions extends Component
{
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

    protected function getRules(): array
    {
        $request = ($this->selectedRole['id'] ?? false) ? new UpdateRoleRequest() : new CreateRoleRequest();

        return Arr::prependKeysWith($request->rules(), 'selectedRole.');
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
        $this->validate();

        $service = new RoleService();

        if ($this->selectedRole['id'] ?? false) {
            $service->update($this->selectedRole);
        } else {
            $role = $service->create($this->selectedRole);
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
