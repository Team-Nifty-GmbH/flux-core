<?php

namespace FluxErp\Rulesets\CountryRegion;

use FluxErp\Models\CountryRegion;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCountryRegionRuleset extends FluxRuleset
{
    protected static ?string $model = CountryRegion::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CountryRegion::class),
            ],
        ];
    }
}
