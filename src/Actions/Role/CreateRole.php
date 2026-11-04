<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Rulesets\Role\CreateRoleRuleset;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends FluxAction
{
    public static function models(): array
    {
        return [Role::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateRoleRuleset::class;
    }

    public function performAction(): Model
    {
        $role = resolve_static(Role::class, 'create', [$this->data]);

        $role->givePermissionTo(array_merge(
            $this->getData('permissions') ?? [],
            resolve_static(Permission::class, 'query')
                ->where('guard_name', $role->guard_name)
                ->where('name', 'like', 'model.%.get')
                ->pluck('id')
                ->all()
        ));

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
