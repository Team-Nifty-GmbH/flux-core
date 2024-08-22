<?php

namespace FluxErp\Rulesets\Role;

use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRoleUsersRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Role::class]),
            ],
            'assign' => 'required|boolean',
            'users' => 'required|array',
            'users.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
        ];
    }
}
