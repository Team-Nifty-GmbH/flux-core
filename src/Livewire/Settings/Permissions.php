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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Permissions extends RoleList
{
    public ?string $includeBefore = 'flux::livewire.settings.permissions';

    public array $permissions = [];

    public RoleForm $roleForm;

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create'))
                ->color('indigo')
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
                ->text(__('Assign users'))
                ->color('indigo')
                ->attributes([
                    'wire:click' => 'editUsers(record.id)',
                ])
                ->when(resolve_static(UpdateRole::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Edit permissions'))
                ->color('indigo')
                ->attributes([
                    'x-cloak',
                    'x-show' => 'record.name !== \'Super Admin\'',
                    'wire:click' => 'edit(record.id)',
                ])
                ->when(resolve_static(UpdateRole::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->attributes([
                    'x-cloak',
                    'x-show' => 'record.name !== \'Super Admin\'',
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Role')]),
                ])
                ->when(resolve_static(DeleteRole::class, 'canPerformAction', [false])),
        ];
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

    #[Renderless]
    public function edit(?Role $role, string $modal = 'edit-role-permissions-modal'): void
    {
        $this->roleForm->reset();
        $this->roleForm->fill($role);
        $this->permissions = $this->getPermissionTree();

        $this->js(<<<JS
            \$modalOpen('$modal');
        JS);
    }

    #[Renderless]
    public function editUsers(Role $role): void
    {
        $this->edit($role, 'edit-role-users-modal');
    }

    #[Renderless]
    public function getPermissionTree(): array
    {
        $permissions = Permission::query()
            ->where('guard_name', $this->roleForm->guard_name)
            ->pluck('id', 'name')
            ->toArray();

        return $this->preparePermissions(Arr::undotToTree(
            array: $permissions,
            translate: fn (string $key) => $key === 'get' ? __('permission.get') : __(Str::headline($key))
        ));
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

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'guards' => array_keys(config('auth.guards')),
                'users' => resolve_static(User::class, 'query')
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    protected function preparePermissions(array $tree, array $parent = []): array
    {
        foreach ($tree as $key => &$value) {
            $label = data_get($value, 'label');

            if ($parent) {
                data_set($tree, $key . '.path', data_get($parent, 'path') . ' -> ' . $label);
            } else {
                data_set($tree, $key . '.path', $label);
            }

            if ($children = data_get($value, 'children')) {
                $value['children'] = $this->preparePermissions($children, $value);
            }
        }

        return $tree;
    }
}
