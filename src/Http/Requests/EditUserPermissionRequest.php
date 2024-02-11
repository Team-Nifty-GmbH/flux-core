<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class EditUserPermissionRequest extends BaseFormRequest
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
            'give' => 'sometimes|required|boolean',
            'permissions' => 'present|array',
            'permissions.*' => [
                'required',
                'integer',
                new ModelExists(Permission::class),
            ],
        ];
    }
}
