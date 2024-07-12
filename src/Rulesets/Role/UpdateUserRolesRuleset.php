<?php

namespace FluxErp\Rulesets\Role;

use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateUserRolesRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'assign' => 'required_without:sync|boolean',
            'sync' => 'boolean',
            'roles' => 'present|array',
            'roles.*' => [
                'required',
                'integer',
                (new ModelExists(Role::class))
                    ->whereIn('guard_name', resolve_static(User::class, 'getGuardNames')),
            ],
        ];
    }
}
