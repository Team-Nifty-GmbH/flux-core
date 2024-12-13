<?php

namespace FluxErp\Rulesets\Role;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
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
            'permissions.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Permission::class]),
            ],
            'users' => 'nullable|array',
            'users.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
        ];
    }
}
