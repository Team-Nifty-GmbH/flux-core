<?php

namespace FluxErp\Rulesets\PaymentRun;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\BankConnection;
use FluxErp\Models\PaymentRun;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\PaymentRun\PaymentRunState;
use Illuminate\Validation\Rule;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreatePaymentRunRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentRun::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:payments,uuid',
            'bank_connection_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => BankConnection::class]),
            ],
            'state' => [
                'string',
                ValidStateRule::make(PaymentRunState::class),
            ],
            'payment_run_type_enum' => [
                'required',
                Rule::enum(PaymentRunTypeEnum::class),
            ],
            'iban' => [
                'nullable',
                app(Iban::class),
            ],
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(OrderRuleset::class, 'getRules')
        );
    }
}
