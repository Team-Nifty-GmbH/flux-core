<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateRoleRequest;
use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateRoleRequest())->rules();
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function execute(): Model
    {
        $role = Role::create($this->data);

        if ($this->data['permissions'] ?? false) {
            $role->givePermissionTo($this->data['permissions']);
        }

        return $role;
    }
}
