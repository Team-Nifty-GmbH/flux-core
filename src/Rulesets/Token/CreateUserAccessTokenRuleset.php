<?php

namespace FluxErp\Rulesets\Token;

use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateUserAccessTokenRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => [
                'required',
                'string',
                Rule::in([
                    'interface',
                    'user',
                ]),
            ],
        ];
    }
}
