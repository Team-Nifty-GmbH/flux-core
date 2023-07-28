<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateRoleRequest;
use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Model;

class CreateRole extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateRoleRequest())->rules();
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): Model
    {
        $role = Role::create($this->data);

        if ($this->data['permissions'] ?? false) {
            $role->givePermissionTo($this->data['permissions']);
        }

        return $role;
    }
}
