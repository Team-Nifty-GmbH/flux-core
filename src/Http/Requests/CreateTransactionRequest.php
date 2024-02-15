<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;

class CreateTransactionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:transactions,uuid',
            'bank_connection_id' => [
                'required',
                'integer',
                new ModelExists(BankConnection::class),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                new ModelExists(Currency::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Transaction::class),
            ],
            'order_id' => [
                'integer',
                'nullable',
                new ModelExists(Order::class),
            ],
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
