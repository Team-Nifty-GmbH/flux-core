<?php

namespace FluxErp\Actions\LedgerBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Scopes\UserTenantScope;
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
        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Bookings are also created programmatically (no authenticated user), so the
        // UserTenantScope on ModelExists cannot enforce the tenant there. Bypass only
        // that scope and check both accounts belong to the booking tenant.
        $onTenant = resolve_static(LedgerAccount::class, 'query')
            ->withoutGlobalScope(UserTenantScope::class)
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
