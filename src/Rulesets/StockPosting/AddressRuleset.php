<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Address;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class AddressRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'address' => 'array',
            'address.id' => [
                'required_with:address',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'address.quantity' => [
                'nullable',
                app(Numeric::class, ['min' => 1]),
            ],
        ];
    }
}
