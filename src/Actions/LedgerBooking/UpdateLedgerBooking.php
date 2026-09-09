<?php

namespace FluxErp\Actions\LedgerBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\LedgerBooking\UpdateLedgerBookingRuleset;
use Illuminate\Validation\ValidationException;

class UpdateLedgerBooking extends FluxAction
{
    public static function models(): array
    {
        return [LedgerBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLedgerBookingRuleset::class;
    }

    public function performAction(): LedgerBooking
    {
        $ledgerBooking = resolve_static(LedgerBooking::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $ledgerBooking->fill($this->getData());
        $ledgerBooking->save();

        return $ledgerBooking->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // The account ids are optional on update, so the ruleset's different rule
        // cannot see both sides. Check the resulting pair against the stored one.
        $ledgerBooking = resolve_static(LedgerBooking::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail(['id', 'debit_ledger_account_id', 'credit_ledger_account_id']);

        $debit = $this->getData('debit_ledger_account_id') ?? $ledgerBooking->debit_ledger_account_id;
        $credit = $this->getData('credit_ledger_account_id') ?? $ledgerBooking->credit_ledger_account_id;

        if ($debit === $credit) {
            throw ValidationException::withMessages([
                'debit_ledger_account_id' => ['The debit and credit account must differ.'],
            ]);
        }
    }
}
