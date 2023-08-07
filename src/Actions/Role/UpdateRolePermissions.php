<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\EditRolePermissionRequest;
use FluxErp\Models\Role;

class UpdateRolePermissions extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->setData($this->data ? array_merge(['give' => true], $this->data) : []);
        $this->rules = (new EditRolePermissionRequest())->rules();
    }

    public static function name(): string
    {
        return 'role.update-permissions';
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): array
    {
        $role = Role::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['give']) {
            $role->givePermissionTo($this->data['permissions']);
        } else {
            foreach ($this->data['permissions'] as $permission) {
                $role->revokePermissionTo($permission);
            }
        }

        return $role->permissions->toArray();
    }
}
