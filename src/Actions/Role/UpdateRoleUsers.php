<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\EditRoleUserRequest;
use FluxErp\Models\Role;
use FluxErp\Models\User;

class UpdateRoleUsers extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->data = $this->data ? array_merge(['assign' => true], $this->data) : [];
        $this->rules = (new EditRoleUserRequest())->rules();
    }

    public static function name(): string
    {
        return 'role.update-users';
    }

    public static function models(): array
    {
        return [Role::class, User::class];
    }

    public function execute(): array
    {
        $role = Role::query()
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
