<?php

namespace FluxErp\Rulesets\Token;

use FluxErp\Models\Token;
use FluxErp\Rulesets\FluxRuleset;

class CreateTokenRuleset extends FluxRuleset
{
    protected static ?string $model = Token::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'abilities' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ];
    }
}
