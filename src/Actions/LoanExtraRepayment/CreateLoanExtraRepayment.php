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
    protected ?Loan $loan = null;

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
        $loan = $this->loan ?? resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();

        $extraRepayment = app(LoanExtraRepayment::class, ['attributes' => $this->getData()]);

        $scheduler = ExtraRepaymentScheduler::make($loan);
        $open = $scheduler->getOpenInstallments();
        $schedule = $scheduler
            ->reschedule($extraRepayment->amount, $extraRepayment->schedule_adjustment_type_enum)
            ->getSchedule();

        $extraRepayment->fill($scheduler->savings());
        $extraRepayment->save();

        $loan->installments()
            ->whereKey($open->modelKeys())
            ->delete();

        foreach ($schedule as $installment) {
            $loan->installments()->create($installment);
        }

        $lastInstallment = array_last($schedule);
        $firstRepayment = array_first(
            array_filter(
                $schedule,
                fn (array $installment): bool => bccomp($installment['principal_amount'], '0', 2) === 1
            )
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

        $loan = $this->loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();

        if (! $loan->allows_extra_repayments) {
            throw ValidationException::withMessages([
                'amount' => [__('This loan does not allow extra repayments.')],
            ]);
        }

        $executedAt = Carbon::parse($this->getData('executed_at'));
        $amount = bcadd((string) $this->getData('amount'), '0', 2);
        $outstanding = bcadd((string) $loan->remaining, '0', 2);
        $remainingAllowance = $loan->remainingExtraRepaymentAllowance($executedAt->year);
        $errors = [];

        if ($executedAt->lt($loan->starts_at)) {
            $errors['executed_at'][] = __('The extra repayment cannot be executed before the loan starts.');
        }

        if (bccomp($amount, $outstanding, 2) === 1) {
            $errors['amount'][] = __('The extra repayment exceeds the outstanding principal of :amount.', [
                'amount' => $outstanding,
            ]);
        }

        if (! is_null($remainingAllowance) && bccomp($amount, $remainingAllowance, 2) === 1) {
            $errors['amount'][] = __('The extra repayment exceeds the allowance of :amount left for :year.', [
                'amount' => $remainingAllowance,
                'year' => $executedAt->year,
            ]);
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
