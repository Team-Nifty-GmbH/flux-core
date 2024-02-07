<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;
use FluxErp\Rules\Numeric;

class CreateTransactionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:transactions,uuid',
            'bank_connection_id' => 'required|integer|exists:bank_connections,id',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'parent_id' => 'integer|nullable|exists:transactions,id',
            'order_id' => 'integer|nullable|exists:orders,id,deleted_at,NULL',
            'value_date' => 'required|date_format:Y-m-d',
            'booking_date' => 'required|date_format:Y-m-d',
            'amount' => [
                'required',
                new Numeric(),
            ],
            'purpose' => 'string|nullable',
            'type' => 'string|nullable',
            'counterpart_name' => 'string|nullable',
            'counterpart_account_number' => 'string|nullable',
            'counterpart_iban' => [
                'string',
                'nullable',
                new Iban(),
            ],
            'counterpart_bic' => 'string|nullable',
            'counterpart_bank_name' => 'string|nullable',
        ];
    }
}
