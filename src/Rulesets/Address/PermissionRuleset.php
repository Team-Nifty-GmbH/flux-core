<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Permission;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class PermissionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'permissions' => 'array',
            'permissions.*' => [
                'required',
                'integer',
                (new ModelExists(Permission::class))->where('guard_name', 'address'),
            ],
        ];
    }
}
