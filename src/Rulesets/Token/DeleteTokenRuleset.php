<?php

namespace FluxErp\Rulesets\Token;

use FluxErp\Models\Token;
use FluxErp\Rules\ModelExists;

class DeleteTokenRuleset
{
    protected static ?string $model = Token::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Token::class),
            ],
        ];
    }
}
