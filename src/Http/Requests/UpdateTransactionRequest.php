<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;

class UpdateTransactionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Transaction::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Transaction::class),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                new ModelExists(Currency::class),
            ],
            'order_id' => [
                'integer',
                'nullable',
                new ModelExists(Order::class),
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
