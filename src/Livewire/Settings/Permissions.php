<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\DeleteRole;
use FluxErp\Actions\Role\UpdateRole;
use FluxErp\Livewire\DataTables\RoleList;
use FluxErp\Livewire\Forms\RoleForm;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Permissions extends RoleList
{
    public ?string $includeBefore = 'flux::livewire.settings.permissions';

    public RoleForm $roleForm;

    public array $permissions = [];

    #[Renderless]
    public function getPermissionTree()
    {
        $permissions = Permission::query()
            ->where('guard_name', $this->roleForm->guard_name)
            ->pluck('id', 'name')
            ->toArray();

        return Arr::undotToTree($permissions);
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'guards' => array_keys(config('auth.guards')),
                'users' => resolve_static(User::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->color('primary')
                ->icon('plus')
                ->attributes([
                    'wire:click' => 'edit()',
                ])
                ->when(resolve_static(CreateRole::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Assign users'))
                ->color('primary')
                ->attributes([
                    'wire:click' => 'editUsers(record.id)',
                ])
                ->when(resolve_static(UpdateRole::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->label(__('Edit permissions'))
                ->color('primary')
                ->attributes([
                    'x-cloak',
                    'x-show' => 'record.name !== \'Super Admin\'',
                    'wire:click' => 'edit(record.id)',
                ])
                ->when(resolve_static(UpdateRole::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->label(__('Delete'))
                ->color('negative')
                ->attributes([
                    'x-cloak',
                    'x-show' => 'record.name !== \'Super Admin\'',
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Role')]),
                ])
                ->when(resolve_static(DeleteRole::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function editUsers(Role $role): void
    {
        $this->edit($role, 'edit-role-users');
    }

    #[Renderless]
    public function edit(?Role $role, string $modal = 'edit-role-permissions'): void
    {
        $this->roleForm->reset();
        $this->roleForm->fill($role);
        $this->permissions = $this->getPermissionTree();

        $this->js(<<<JS
            \$openModal('$modal');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->roleForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function delete(Role $role): bool
    {
        $this->roleForm->reset();
        $this->roleForm->fill($role);

        try {
            $this->roleForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
