<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rulesets\Role\UpdateRoleRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateRole extends FluxAction
{
    public static function models(): array
    {
        return [Role::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateRoleRuleset::class;
    }

    public function performAction(): Model
    {
        $role = resolve_static(Role::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $role->fill($this->data);
        $role->save();

        if (! is_null($permissions = $this->getData('permissions'))) {
            $role->syncPermissions($permissions);
        }

        if (! is_null($users = $this->getData('users'))) {
            $affected = array_merge($users, $role->users()->pluck('users.id')->all());
            $withoutRoleBefore = resolve_static(User::class, 'idsWithoutRole', [$affected]);

            $role->users()->sync($users);
            resolve_static(User::class, 'syncDirectModelGetPermissions', [$affected, $withoutRoleBefore]);
        }

        return $role->withoutRelations()->fresh();
    }
}
