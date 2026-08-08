<?php

namespace FluxErp\Actions\Loan;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Loan\CreateLoanRuleset;
use FluxErp\Support\Calculation\RepaymentScheduleGenerator;
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
            $loan->installment_interval_enum,
            $this->getData('grace_period_installments') ?? 0,
            $this->getData('installment_amount'),
        );

        foreach ($schedule as $installment) {
            $loan->installments()->create($installment);
        }

        // the grace period installments carry interest only, the repayment
        // rate is the first one that pays off principal
        $firstRepayment = array_first(
            array_filter($schedule, fn (array $installment): bool => bccomp($installment['principal_amount'], '0', 2) === 1)
        );
        $lastInstallment = array_last($schedule);

        $loan->fill([
            'installment_amount' => $this->getData('installment_amount')
                ?? ($firstRepayment
                    ? bcadd($firstRepayment['principal_amount'], $firstRepayment['interest_amount'], 2)
                    : null),
            'ends_at' => $this->getData('ends_at') ?? $lastInstallment['due_date'] ?? null,
        ]);
        $loan->calculateRemaining()
            ->calculateTotalInterest()
            ->calculateProgress()
            ->save();

        return $loan->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')->getKey();
        $this->data['installment_interval_enum'] ??= InstallmentIntervalEnum::Monthly->value;
        $this->data['grace_period_installments'] ??= 0;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $tenantId = $this->getData('tenant_id');
        $errors = [];

        // Contacts are shared across tenants through the contact_tenant pivot,
        // while ledger accounts and orders carry a tenant_id column.
        if (resolve_static(Contact::class, 'query')
            ->whereKey($this->getData('contact_id'))
            ->whereHasTenant($tenantId)
            ->doesntExist()
        ) {
            $errors['contact_id'] = [__('The selected :attribute must belong to the loan tenant.', ['attribute' => 'contact_id'])];
        }

        if (resolve_static(LedgerAccount::class, 'query')
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
