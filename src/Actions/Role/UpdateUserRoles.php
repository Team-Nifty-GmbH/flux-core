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
        $withoutRoleBefore = resolve_static(User::class, 'idsWithoutRole', [[$user->getKey()]]);

        if ($this->data['sync'] ?? false) {
            $user->syncRoles($roles);
        } elseif ($this->data['assign']) {
            $user->assignRole($roles);
        } else {
            foreach ($roles as $role) {
                $user->removeRole($role);
            }
        }

        resolve_static(
            User::class,
            'syncDirectModelGetPermissions',
            [[$user->getKey()], $withoutRoleBefore]
        );

        return $user->roles->toArray();
    }
}
