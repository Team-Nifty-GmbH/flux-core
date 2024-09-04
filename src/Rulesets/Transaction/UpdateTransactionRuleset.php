<?php

namespace FluxErp\Rulesets\Transaction;

use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateTransactionRuleset extends FluxRuleset
{
    protected static ?string $model = Transaction::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'order_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'value_date' => 'sometimes|required|date',
            'booking_date' => 'sometimes|required|date',
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class),
            ],
            'purpose' => 'string|nullable',
            'type' => 'string|nullable',
            'counterpart_name' => 'string|nullable',
            'counterpart_account_number' => 'string|nullable',
            'counterpart_iban' => [
                'string',
                'nullable',
                app(Iban::class),
            ],
            'counterpart_bic' => 'string|nullable',
            'counterpart_bank_name' => 'string|nullable',
        ];
    }
}
