<?php

namespace FluxErp\Rulesets\Transaction;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateTransactionRuleset extends FluxRuleset
{
    protected static ?string $model = Transaction::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:transactions,uuid',
            'bank_connection_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => BankConnection::class]),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'order_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'value_date' => 'required|date',
            'booking_date' => 'required|date',
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'purpose' => 'string|nullable',
            'type' => 'string|max:255|nullable',
            'counterpart_name' => 'string|max:255|nullable',
            'counterpart_account_number' => 'string|max:255|nullable',
            'counterpart_iban' => [
                'string',
                'nullable',
                'max:255',
                app(Iban::class),
            ],
            'counterpart_bic' => 'string|max:255|nullable',
            'counterpart_bank_name' => 'string|max:255|nullable',
            'is_ignored' => 'boolean',
        ];
    }
}
