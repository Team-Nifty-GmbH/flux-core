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
            'name' => 'required|string|max:255',
            'iso' => 'required|string|max:255|unique:currencies,iso',
            'symbol' => 'required|string|max:255',
            'is_default' => 'boolean',
        ];
    }
}
