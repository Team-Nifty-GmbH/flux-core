<?php

namespace FluxErp\Rulesets\Permission;

use FluxErp\Models\Permission;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePermissionRuleset extends FluxRuleset
{
    protected static ?string $model = Permission::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Permission::class]),
            ],
        ];
    }
}
