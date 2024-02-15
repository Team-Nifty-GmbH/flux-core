<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class EditRoleUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Role::class),
            ],
            'users' => 'required|array',
            'users.*' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
        ];
    }
}
