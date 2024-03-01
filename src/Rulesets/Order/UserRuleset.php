<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UserRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'users' => 'array',
            'users.*' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
        ];
    }
}
