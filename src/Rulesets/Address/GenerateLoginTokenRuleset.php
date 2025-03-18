<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class GenerateLoginTokenRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Address::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class])
                    ->where('can_login', true),
            ],
        ];
    }
}
