<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
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
            $role->users()->sync($users);
        }

        return $role->withoutRelations()->fresh();
    }
}
