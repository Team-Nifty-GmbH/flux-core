<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteAddressRuleset extends FluxRuleset
{
    protected static ?string $model = Address::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
        ];
    }
}
