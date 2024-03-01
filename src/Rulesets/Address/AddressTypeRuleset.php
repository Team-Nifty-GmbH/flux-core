<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Rules\ExistsWithForeign;
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
                new ExistsWithForeign(foreignAttribute: 'client_id', table: 'address_types', baseTable: 'addresses'),
            ],
        ];
    }
}
