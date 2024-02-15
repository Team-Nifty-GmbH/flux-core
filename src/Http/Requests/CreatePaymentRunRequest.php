<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Order;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\States\PaymentRun\PaymentRunState;
use Illuminate\Validation\Rule;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreatePaymentRunRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:payments,uuid',
            'bank_connection_id' => [
                'nullable',
                'integer',
                new ModelExists(BankConnection::class),
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
                new Iban(),
            ],
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',

            'orders' => 'required|array',
            'orders.*.order_id' => [
                'required',
                'integer',
                new ModelExists(Order::class),
            ],
            'orders.*.amount' => 'required|numeric|not_in:0',
        ];
    }
}
