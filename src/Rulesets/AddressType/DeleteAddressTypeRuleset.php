<?php

namespace FluxErp\Rulesets\AddressType;

use FluxErp\Models\AddressType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteAddressTypeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = AddressType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AddressType::class]),
            ],
        ];
    }
}
