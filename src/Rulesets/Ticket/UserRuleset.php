<?php

namespace FluxErp\Rulesets\Ticket;

use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UserRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'users' => 'array|nullable',
            'users.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
        ];
    }
}
