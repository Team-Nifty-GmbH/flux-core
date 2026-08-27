<?php

namespace FluxErp\Actions\LoanExtraRepayment;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanExtraRepayment;
use FluxErp\Rulesets\LoanExtraRepayment\CreateLoanExtraRepaymentRuleset;
use FluxErp\Support\Calculation\ExtraRepaymentScheduler;
use Illuminate\Validation\ValidationException;

class CreateLoanExtraRepayment extends FluxAction
{
    public static function models(): array
    {
        return [LoanExtraRepayment::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLoanExtraRepaymentRuleset::class;
    }

    public function performAction(): LoanExtraRepayment
    {
        $loan = $this->loan();
        $scheduler = app(ExtraRepaymentScheduler::class);

        $extraRepayment = app(LoanExtraRepayment::class, ['attributes' => $this->getData()]);

        $open = $scheduler->openInstallments($loan);
        $schedule = $scheduler->reschedule(
            $loan,
            $extraRepayment->amount,
            $extraRepayment->schedule_adjustment_type_enum,
            $open
        );

        $extraRepayment->fill($scheduler->savings($schedule, $open));
        $extraRepayment->save();

        $loan->installments()
            ->whereIn('id', $open->modelKeys())
            ->delete();

        foreach ($schedule as $installment) {
            $loan->installments()->create($installment);
        }

        $lastInstallment = array_last($schedule);
        $firstRepayment = array_first(
            array_filter($schedule, fn (array $installment): bool => bccomp($installment['principal_amount'], '0', 2) === 1)
        );

        $loan->fill([
            'installment_amount' => $firstRepayment
                ? bcadd($firstRepayment['principal_amount'], $firstRepayment['interest_amount'], 2)
                : $loan->installment_amount,
            'ends_at' => $lastInstallment
                ? $lastInstallment['due_date']
                : $loan->installments()->max('due_date'),
        ]);
        $loan->calculateRemaining()
            ->calculateTotalInterest()
            ->calculateProgress()
            ->save();

        return $extraRepayment->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $loan = $this->loan();

        if (! $loan->allows_extra_repayments) {
            throw ValidationException::withMessages([
                'amount' => [__('This loan does not allow extra repayments.')],
            ]);
        }

        $amount = bcadd((string) $this->getData('amount'), '0', 2);
        $outstanding = bcadd((string) $loan->remaining, '0', 2);

        if (bccomp($amount, $outstanding, 2) === 1) {
            throw ValidationException::withMessages([
                'amount' => [
                    __('The extra repayment exceeds the outstanding principal of :amount.', [
                        'amount' => $outstanding,
                    ]),
                ],
            ]);
        }

        $remainingAllowance = $loan->remainingExtraRepaymentAllowance(
            Carbon::parse($this->getData('executed_at'))->year
        );

        if (! is_null($remainingAllowance) && bccomp($amount, $remainingAllowance, 2) === 1) {
            throw ValidationException::withMessages([
                'amount' => [
                    __('The extra repayment exceeds the allowance of :amount left for :year.', [
                        'amount' => $remainingAllowance,
                        'year' => Carbon::parse($this->getData('executed_at'))->year,
                    ]),
                ],
            ]);
        }
    }

    protected function loan(): Loan
    {
        return resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();
    }
}
