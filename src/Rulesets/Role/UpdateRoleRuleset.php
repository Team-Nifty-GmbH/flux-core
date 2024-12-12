<?php

namespace FluxErp\Rulesets\Role;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRoleRuleset extends FluxRuleset
{
    protected static ?string $model = Role::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Role::class]),
            ],
            'name' => 'sometimes|required|string',
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
