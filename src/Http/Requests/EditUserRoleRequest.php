<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class EditUserRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'sync' => 'sometimes|required|boolean',
            'assign' => 'sometimes|required|boolean',
            'roles' => 'present|array',
            'roles.*' => [
                'required',
                'integer',
                new ModelExists(Role::class),
            ],
        ];
    }
}
