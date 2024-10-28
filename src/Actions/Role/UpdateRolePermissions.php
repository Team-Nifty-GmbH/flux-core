<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Rulesets\Role\UpdateRolePermissionsRuleset;

class UpdateRolePermissions extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->setData($this->data ? array_merge(['give' => true], $this->data) : []);
    }

    public static function name(): string
    {
        return 'role.update-permissions';
    }

    protected function getRulesets(): string|array
    {
        return UpdateRolePermissionsRuleset::class;
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): array
    {
        $role = resolve_static(Role::class, 'query')
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
