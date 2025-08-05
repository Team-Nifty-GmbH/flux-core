<?php

namespace FluxErp\Rulesets\PaymentType;

use FluxErp\Models\PaymentType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePaymentTypeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PaymentType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentType::class]),
            ],
        ];
    }
}
