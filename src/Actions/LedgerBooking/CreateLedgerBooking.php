<?php

namespace FluxErp\Actions\LedgerBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\LedgerBooking\CreateLedgerBookingRuleset;
use Illuminate\Validation\ValidationException;

class CreateLedgerBooking extends FluxAction
{
    public static function models(): array
    {
        return [LedgerBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLedgerBookingRuleset::class;
    }

    public function performAction(): LedgerBooking
    {
        $ledgerBooking = app(LedgerBooking::class, ['attributes' => $this->getData()]);
        $ledgerBooking->save();

        return $ledgerBooking->refresh();
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Bypass the tenant scope so a mismatched account is detected instead of
        // silently filtered out of the lookup.
        $onTenant = resolve_static(LedgerAccount::class, 'query')
            ->withoutGlobalScopes()
            ->whereKey([$this->getData('debit_ledger_account_id'), $this->getData('credit_ledger_account_id')])
            ->where('tenant_id', $this->getData('tenant_id'))
            ->count();

        if ($onTenant !== 2) {
            throw ValidationException::withMessages([
                'debit_ledger_account_id' => ['The ledger accounts must belong to the booking tenant.'],
            ]);
        }
    }
}
