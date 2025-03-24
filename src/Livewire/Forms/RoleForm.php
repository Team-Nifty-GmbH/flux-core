<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\DeleteRole;
use FluxErp\Actions\Role\UpdateRole;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use Livewire\Attributes\Locked;

class RoleForm extends FluxForm
{
    public ?string $guard_name = null;

    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public array $permissions = [];

    public ?array $users = null;

    public function fill($values): void
    {
        parent::fill($values);

        if ($values instanceof Role) {
            $values->loadMissing(['permissions:name', 'users:id']);
            $this->permissions = $values->permissions
                ->pluck('name')
                ->toArray();
            $this->users = $values->users->pluck('id')->toArray();
        }
    }

    public function toActionData(): array
    {
        $actionData = parent::toActionData();

        $actionData['permissions'] = resolve_static(Permission::class, 'query')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', $this->guard_name)
            ->pluck('id')
            ->toArray();

        return $actionData;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateRole::class,
            'update' => UpdateRole::class,
            'delete' => DeleteRole::class,
        ];
    }
}
