<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Rules\Iban;
use FluxErp\States\PaymentRun\PaymentRunState;
use Illuminate\Validation\Rule;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreatePaymentRunRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:payments,uuid',
            'bank_connection_id' => 'nullable|integer|exists:bank_connections,id,deleted_at,NULL',
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
                new Iban(),
            ],
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',

            'orders' => 'required|array',
            'orders.*.order_id' => 'required|integer|exists:orders,id,deleted_at,NULL',
            'orders.*.amount' => 'required|numeric|not_in:0',
        ];
    }
}
