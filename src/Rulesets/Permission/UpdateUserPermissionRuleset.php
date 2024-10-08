<?php

namespace FluxErp\Rulesets\Permission;

use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateUserPermissionRuleset extends FluxRuleset
{
    protected static ?string $model = Permission::class;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'sync' => 'sometimes|required|boolean',
            'give' => 'sometimes|required|boolean',

            'permissions' => 'present|array',
            'permissions.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Permission::class]),
            ],
        ];
    }
}
