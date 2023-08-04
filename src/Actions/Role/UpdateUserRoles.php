<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\EditUserRoleRequest;
use FluxErp\Models\Role;
use FluxErp\Models\User;

class UpdateUserRoles extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->setData($this->data ? array_merge(['assign' => true], $this->data) : []);
        $this->rules = (new EditUserRoleRequest())->rules();
    }

    public static function name(): string
    {
        return 'user.update-roles';
    }

    public static function models(): array
    {
        return [Role::class, User::class];
    }

    public function performAction(): array
    {
        $user = User::query()
            ->whereKey($this->data['user_id'])
            ->first();

        if ($this->data['sync']) {
            $user->syncRoles($this->data['roles']);

            return $user->roles->toArray();
        }

        if ($this->data['assign']) {
            $user->assignRole($this->data['roles']);
        } else {
            foreach ($this->data['roles'] as $role) {
                $user->removeRole($role);
            }
        }

        return $user->roles->toArray();
    }
}
