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
                app(ModelExists::class, ['model' => Role::class]),
            ],
            'give' => 'required|boolean',
            'permissions' => 'required|array',
            'permissions.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Permission::class]),
            ],
        ];
    }
}
