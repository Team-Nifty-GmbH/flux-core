<?php

namespace FluxErp\Rulesets\Currency;

use FluxErp\Models\Currency;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCurrencyRuleset extends FluxRuleset
{
    protected static ?string $model = Currency::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
        ];
    }
}
