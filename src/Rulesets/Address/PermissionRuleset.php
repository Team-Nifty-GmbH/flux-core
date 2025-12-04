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
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Permission::class])->where('guard_name', 'address'),
            ],
        ];
    }
}
