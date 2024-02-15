<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Rules\ModelExists;

class UpdateRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Role::class),
            ],
            'name' => 'sometimes|required|string',
            'permissions' => 'array',
            'permissions.*' => [
                'required',
                'integer',
                new ModelExists(Permission::class),
            ],
        ];
    }
}
