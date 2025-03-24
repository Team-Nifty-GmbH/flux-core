<?php

namespace FluxErp\Rulesets\Token;

use FluxErp\Models\Token;
use FluxErp\Rules\ModelExists;

class DeleteTokenRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Token::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Token::class]),
            ],
        ];
    }
}
