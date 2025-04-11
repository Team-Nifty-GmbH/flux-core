<?php

namespace FluxErp\Rulesets\Permission;

use FluxErp\Models\Permission;
use FluxErp\Rulesets\FluxRuleset;

class CreatePermissionRuleset extends FluxRuleset
{
    protected static ?string $model = Permission::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'required|string|max:255',
        ];
    }
}
