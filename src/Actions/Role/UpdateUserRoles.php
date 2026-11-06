<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rulesets\Role\UpdateUserRolesRuleset;

class UpdateUserRoles extends FluxAction
{
    public static function models(): array
    {
        return [Role::class, User::class];
    }

    public static function name(): string
    {
        return 'user.update-roles';
    }

    protected function getRulesets(): string|array
    {
        return UpdateUserRolesRuleset::class;
    }

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->data = $this->data ? array_merge(['assign' => true], $this->data) : [];
    }

    public function performAction(): array
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($this->data['user_id'])
            ->first();
        $roles = Role::query()
            ->whereKey($this->data['roles'])
            ->get();

        if ($this->data['sync'] ?? false) {
            $user->syncRoles($roles);
            resolve_static(User::class, 'revokeDirectModelGetPermissions', [[$user->getKey()]]);

            return $user->roles->toArray();
        }

        if ($this->data['assign']) {
            $user->assignRole($roles);
            resolve_static(User::class, 'revokeDirectModelGetPermissions', [[$user->getKey()]]);
        } else {
            foreach ($roles as $role) {
                $user->removeRole($role);
            }
        }

        return $user->roles->toArray();
    }
}
