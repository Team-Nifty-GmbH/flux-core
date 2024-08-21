<?php

namespace FluxErp\Rulesets\TicketType;

use FluxErp\Models\Role;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class RoleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'roles' => 'array',
            'roles.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Role::class]),
            ],
        ];
    }
}
