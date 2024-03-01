<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rulesets\Role\UpdateRoleUsersRuleset;

class UpdateRoleUsers extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->setData($this->data ? array_merge(['assign' => true], $this->data) : []);
        $this->rules = resolve_static(UpdateRoleUsersRuleset::class, 'getRules');
    }

    public static function name(): string
    {
        return 'role.update-users';
    }

    public static function models(): array
    {
        return [Role::class, User::class];
    }

    public function performAction(): array
    {
        $role = app(Role::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['assign']) {
            $role->users()->syncWithoutDetaching($this->data['users']);
        } else {
            $role->users()->detach($this->data['users']);
        }

        return $role->users->toArray();
    }
}
