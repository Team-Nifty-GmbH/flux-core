<?php

namespace FluxErp\Rulesets\Country;

use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
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
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'language_id' => [
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'currency_id' => [
                'integer',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'name' => 'string',
            'iso_alpha2' => 'sometimes|required|string|unique:countries,iso_alpha2',
            'iso_alpha3' => 'nullable|string',
            'iso_numeric' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 999]),
            ],
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_eu_country' => 'boolean',
        ];
    }
}
