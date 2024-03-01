<?php

namespace FluxErp\Rulesets\Role;

use FluxErp\Models\Role;
use FluxErp\Rulesets\FluxRuleset;

class CreateRoleRuleset extends FluxRuleset
{
    protected static ?string $model = Role::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'required|string',
            'permissions' => 'array',
            'permissions.*' => 'required|integer|exists:permissions,id',
        ];
    }
}
