<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\AddressType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class AddressTypeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'address_types' => 'array',
            'address_types.*' => [
                'required',
                'integer',
                'distinct',
                app(ModelExists::class, ['model' => AddressType::class]),
            ],
        ];
    }
}
