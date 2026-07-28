<?php

namespace FluxErp\Actions\Loan;

use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

trait ValidatesLoanRelationsOnTenant
{
    protected function assertLoanRelationsOnTenant(int $tenantId): void
    {
        // Contacts are shared across tenants through the contact_tenant pivot,
        // while ledger accounts and orders carry a tenant_id column.
        if (! is_null($this->getData('contact_id'))) {
            $this->assertRelationOnTenant(
                resolve_static(Contact::class, 'query')
                    ->whereKey($this->getData('contact_id'))
                    ->whereHasTenant($tenantId),
                'contact_id'
            );
        }

        if (! is_null($this->getData('ledger_account_id'))) {
            $this->assertRelationOnTenant(
                resolve_static(LedgerAccount::class, 'query')
                    ->withoutGlobalScopes()
                    ->whereKey($this->getData('ledger_account_id'))
                    ->where('tenant_id', $tenantId),
                'ledger_account_id'
            );
        }

        if (! is_null($this->getData('order_id'))) {
            $this->assertRelationOnTenant(
                resolve_static(Order::class, 'query')
                    ->withoutGlobalScopes()
                    ->whereKey($this->getData('order_id'))
                    ->where('tenant_id', $tenantId),
                'order_id'
            );
        }
    }

    protected function assertRelationOnTenant(Builder $query, string $field): void
    {
        if (! $query->exists()) {
            throw ValidationException::withMessages([
                $field => [__('The selected :attribute must belong to the loan tenant.', ['attribute' => $field])],
            ]);
        }
    }
}
