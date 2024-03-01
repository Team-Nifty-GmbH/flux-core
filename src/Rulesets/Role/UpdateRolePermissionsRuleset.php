<?php

namespace FluxErp\Rulesets\Role;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRolePermissionsRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Role::class),
            ],
            'give' => 'required|boolean',
            'permissions' => 'required|array',
            'permissions.*' => [
                'required',
                'integer',
                new ModelExists(Permission::class),
            ],
        ];
    }
}
