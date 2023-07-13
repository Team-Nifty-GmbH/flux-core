<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateRoleRequest;
use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Model;

class UpdateRole extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateRoleRequest())->rules();
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function execute(): Model
    {
        $role = Role::query()
            ->whereKey($this->data['id'])
            ->first();

        $role->fill($this->data);
        $role->save();

        if ($this->data['permissions'] ?? false) {
            $role->syncPermissions($this->data['permissions']);
        }

        return $role->withoutRelations()->fresh();
    }
}
