<?php

namespace FluxErp\Rulesets\LedgerBooking;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLedgerBookingRuleset extends FluxRuleset
{
    protected static ?string $model = LedgerBooking::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerBooking::class]),
            ],
            'credit_ledger_account_id' => [
                'sometimes',
                'required',
                'integer',
                'different:debit_ledger_account_id',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'debit_ledger_account_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'booking_date' => 'sometimes|required|date',
            'booking_text' => 'string|max:255|nullable',
            'note' => 'string|max:255|nullable',
        ];
    }
}
