<?php

namespace FluxErp\Actions\Loan;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Loan\CreateLoanRuleset;
use FluxErp\Support\Calculation\RepaymentScheduleGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class CreateLoan extends FluxAction
{
    public static function models(): array
    {
        return [Loan::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLoanRuleset::class;
    }

    public function performAction(): Loan
    {
        $loan = app(Loan::class, ['attributes' => $this->getData()]);
        $loan->save();

        $schedule = app(RepaymentScheduleGenerator::class)->generate(
            $this->getData('amount'),
            $this->getData('interest_rate'),
            $this->getData('number_of_installments'),
            $loan->repayment_type_enum,
            Carbon::parse($this->getData('starts_at')),
        );

        foreach ($schedule as $installment) {
            $loan->installments()->create($installment);
        }

        $firstInstallment = $schedule[0] ?? null;
        $lastInstallment = $schedule[count($schedule) - 1] ?? null;

        $loan->fill([
            'installment_amount' => $this->getData('installment_amount')
                ?? ($firstInstallment
                    ? bcadd($firstInstallment['principal_amount'], $firstInstallment['interest_amount'], 2)
                    : null),
            'ends_at' => $this->getData('ends_at') ?? $lastInstallment['due_date'] ?? null,
        ]);
        $loan->save();

        return $loan->refresh();
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $tenantId = $this->getData('tenant_id');

        // Contacts are shared across tenants through the contact_tenant pivot,
        // while ledger accounts and orders carry a tenant_id column.
        $this->assertExists(
            resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('contact_id'))
                ->whereHas('tenants', fn (Builder $query) => $query->whereKey($tenantId)),
            'contact_id'
        );

        $this->assertExists(
            resolve_static(LedgerAccount::class, 'query')
                ->withoutGlobalScopes()
                ->whereKey($this->getData('ledger_account_id'))
                ->where('tenant_id', $tenantId),
            'ledger_account_id'
        );

        if (! is_null($this->getData('order_id'))) {
            $this->assertExists(
                resolve_static(Order::class, 'query')
                    ->withoutGlobalScopes()
                    ->whereKey($this->getData('order_id'))
                    ->where('tenant_id', $tenantId),
                'order_id'
            );
        }
    }

    protected function assertExists(Builder $query, string $field): void
    {
        if (! $query->exists()) {
            throw ValidationException::withMessages([
                $field => [__('The selected :attribute must belong to the loan tenant.', ['attribute' => $field])],
            ]);
        }
    }
}
