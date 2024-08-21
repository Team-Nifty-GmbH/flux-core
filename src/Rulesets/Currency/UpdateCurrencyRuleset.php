<?php

namespace FluxErp\Rulesets\Currency;

use FluxErp\Models\Currency;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCurrencyRuleset extends FluxRuleset
{
    protected static ?string $model = Currency::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'name' => 'string',
            'iso' => 'string|unique:currencies,iso',
            'symbol' => 'string',
            'is_default' => 'boolean',
        ];
    }
}
