<?php

namespace FluxErp\Actions\Loan;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Loan;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Loan\CreateLoanRuleset;
use FluxErp\Support\Calculation\RepaymentScheduleGenerator;

class CreateLoan extends FluxAction
{
    use ValidatesLoanRelationsOnTenant;

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

        $firstInstallment = array_first($schedule);
        $lastInstallment = array_last($schedule);

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
        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->assertLoanRelationsOnTenant($this->getData('tenant_id'));
    }
}
