<?php

namespace FluxErp\Rulesets\PaymentRun;

use FluxErp\Models\PaymentRun;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePaymentRunRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PaymentRun::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentRun::class]),
            ],
        ];
    }
}
