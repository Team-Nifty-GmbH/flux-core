<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Rulesets\Role\CreateRoleRuleset;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateRoleRuleset::class;
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): Model
    {
        $role = resolve_static(Role::class, 'create', [$this->data]);

        if ($permissions = $this->getData('permissions')) {
            $role->givePermissionTo($permissions);
        }

        if ($users = $this->getData('users')) {
            $role->users()->attach($users);
        }

        return $role->fresh()->load('permissions');
    }

    protected function prepareForValidation(): void
    {
        $this->data['guard_name'] = $this->data['guard_name'] ?? array_keys(config('auth.guards'))[0];
    }
}
