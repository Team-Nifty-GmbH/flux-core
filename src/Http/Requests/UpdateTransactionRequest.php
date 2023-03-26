<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\Iban;
use FluxErp\Rules\Numeric;

class UpdateTransactionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:transactions,id',
            'parent_id' => 'integer|nullable|exists:transactions,id',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'order_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('orders', 'id'))->whereNull('deleted_at'),
            ],
            'value_date' => 'sometimes|required|date_format:Y-m-d',
            'booking_date' => 'sometimes|required|date_format:Y-m-d',
            'amount' => [
                'sometimes',
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
