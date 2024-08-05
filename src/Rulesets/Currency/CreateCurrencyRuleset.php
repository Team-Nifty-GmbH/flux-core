<?php

namespace FluxErp\Rulesets\Currency;

use FluxErp\Models\Currency;
use FluxErp\Rulesets\FluxRuleset;

class CreateCurrencyRuleset extends FluxRuleset
{
    protected static ?string $model = Currency::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:currencies,uuid',
            'name' => 'required|string',
            'iso' => 'required|string|unique:currencies,iso',
            'symbol' => 'required|string',
            'is_default' => 'boolean',
        ];
    }
}
