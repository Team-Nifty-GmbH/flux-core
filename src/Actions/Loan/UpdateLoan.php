<?php

namespace FluxErp\Actions\Loan;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Loan\UpdateLoanRuleset;
use Illuminate\Validation\ValidationException;

class UpdateLoan extends FluxAction
{
    public static function models(): array
    {
        return [Loan::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLoanRuleset::class;
    }

    public function performAction(): Loan
    {
        $loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $loan->fill($this->getData());
        $loan->save();

        return $loan->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $tenantId = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('id'))
            ->value('tenant_id');

        $errors = [];

        // Contacts are shared across tenants through the contact_tenant pivot,
        // while ledger accounts and orders carry a tenant_id column.
        if (! is_null($this->getData('contact_id'))
            && resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('contact_id'))
                ->whereHasTenant($tenantId)
                ->doesntExist()
        ) {
            $errors['contact_id'] = [__('The selected :attribute must belong to the loan tenant.', ['attribute' => 'contact_id'])];
        }

        if (! is_null($this->getData('ledger_account_id'))
            && resolve_static(LedgerAccount::class, 'query')
                ->withoutGlobalScopes()
                ->whereKey($this->getData('ledger_account_id'))
                ->where('tenant_id', $tenantId)
                ->doesntExist()
        ) {
            $errors['ledger_account_id'] = [__('The selected :attribute must belong to the loan tenant.', ['attribute' => 'ledger_account_id'])];
        }

        if (! is_null($this->getData('order_id'))
            && resolve_static(Order::class, 'query')
                ->withoutGlobalScopes()
                ->whereKey($this->getData('order_id'))
                ->where('tenant_id', $tenantId)
                ->doesntExist()
        ) {
            $errors['order_id'] = [__('The selected :attribute must belong to the loan tenant.', ['attribute' => 'order_id'])];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
