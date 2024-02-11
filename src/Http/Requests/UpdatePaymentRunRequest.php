<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\BankConnection;
use FluxErp\Models\PaymentRun;
use FluxErp\Rules\ModelExists;
use FluxErp\States\PaymentRun\PaymentRunState;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdatePaymentRunRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PaymentRun::class),
            ],
            'bank_connection_id' => [
                'nullable',
                'integer',
                new ModelExists(BankConnection::class),
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
