<?php

namespace FluxErp\Rulesets\PaymentRun;

use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Models\BankConnection;
use FluxErp\Models\PaymentRun;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\PaymentRun\PaymentRunState;
use Illuminate\Validation\Rule;

class UpdatePaymentRunRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentRun::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentRun::class]),
            ],
            'bank_connection_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => BankConnection::class]),
            ],
            'sepa_mandate_type_enum' => [
                'nullable',
                Rule::enum(SepaMandateTypeEnum::class),
            ],
            'state' => [
                'string',
                ValidStateRule::make(PaymentRunState::class),
            ],
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',
        ];
    }
}
