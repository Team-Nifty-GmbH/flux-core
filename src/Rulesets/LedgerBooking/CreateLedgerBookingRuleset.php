<?php

namespace FluxErp\Rulesets\LedgerBooking;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Tenant;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateLedgerBookingRuleset extends FluxRuleset
{
    protected static ?string $model = LedgerBooking::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:ledger_bookings,uuid',
            'credit_ledger_account_id' => [
                'required',
                'integer',
                'different:debit_ledger_account_id',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'debit_ledger_account_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'amount' => [
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'booking_date' => 'required|date',
            'booking_text' => 'string|max:255|nullable',
            'note' => 'string|max:255|nullable',
        ];
    }
}
