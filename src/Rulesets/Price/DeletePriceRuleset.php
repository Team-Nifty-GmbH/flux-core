<?php

namespace FluxErp\Rulesets\Price;

use FluxErp\Models\PaymentType;
use FluxErp\Models\Price;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePriceRuleset extends FluxRuleset
{
    protected static ?string $model = Price::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Price::class),
            ],
        ];
    }
}
