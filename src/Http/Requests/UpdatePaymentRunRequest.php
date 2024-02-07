<?php

namespace FluxErp\Http\Requests;

use FluxErp\States\PaymentRun\PaymentRunState;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdatePaymentRunRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:payment_runs,id',
            'bank_connection_id' => 'nullable|integer|exists:bank_connections,id,deleted_at,NULL',
            'state' => [
                'string',
                ValidStateRule::make(PaymentRunState::class),
            ],
            'instructed_execution_date' => 'date',
            'is_instant_payment' => 'boolean',
        ];
    }
}
