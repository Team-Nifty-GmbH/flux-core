<?php

namespace FluxErp\Rulesets\Country;

use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCountryRuleset extends FluxRuleset
{
    protected static ?string $model = Country::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Country::class),
            ],
            'language_id' => [
                'integer',
                new ModelExists(Language::class),
            ],
            'currency_id' => [
                'integer',
                new ModelExists(Currency::class),
            ],
            'name' => 'string',
            'iso_alpha2' => 'sometimes|required|string|unique:countries,iso_alpha2',
            'iso_alpha3' => 'string|nullable',
            'iso_numeric' => 'string|nullable',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_eu_country' => 'boolean',
        ];
    }
}
