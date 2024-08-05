<?php

namespace FluxErp\Rulesets\Country;

use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCountryRuleset extends FluxRuleset
{
    protected static ?string $model = Country::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:countries,uuid',
            'language_id' => [
                'required',
                'integer',
                new ModelExists(Language::class),
            ],
            'currency_id' => [
                'required',
                'integer',
                new ModelExists(Currency::class),
            ],
            'name' => 'required|string',
            'iso_alpha2' => 'required|string|unique:countries,iso_alpha2',
            'iso_alpha3' => 'string',
            'iso_numeric' => 'string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_eu_country' => 'boolean',
        ];
    }
}
