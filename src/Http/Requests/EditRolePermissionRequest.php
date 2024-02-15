<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Rules\ModelExists;

class EditRolePermissionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Role::class),
            ],
            'permissions' => 'required|array',
            'permissions.*' => [
                'required',
                'integer',
                new ModelExists(Permission::class),
            ],
        ];
    }
}
