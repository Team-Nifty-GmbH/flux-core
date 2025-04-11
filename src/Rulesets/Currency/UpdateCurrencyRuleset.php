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
            'name' => 'string|max:255',
            'iso' => 'string|max:255|unique:currencies,iso',
            'symbol' => 'string|max:255',
            'is_default' => 'boolean',
        ];
    }
}
