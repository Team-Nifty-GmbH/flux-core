<?php

namespace FluxErp\Rulesets\Country;

use FluxErp\Models\Country;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCountryRuleset extends FluxRuleset
{
    protected static ?string $model = Country::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
        ];
    }
}
