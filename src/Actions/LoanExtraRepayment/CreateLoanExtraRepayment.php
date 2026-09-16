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
        $this->loan ??= resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();

        $extraRepayment = app(LoanExtraRepayment::class, ['attributes' => $this->getData()]);

        $scheduler = ExtraRepaymentScheduler::make($this->loan);
        $open = $scheduler->getOpenInstallments();
        $schedule = $scheduler
            ->reschedule($extraRepayment->amount, $extraRepayment->schedule_adjustment_type_enum)
            ->getSchedule();

        $extraRepayment->fill($scheduler->savings());
        $extraRepayment->save();

        $this->loan->installments()
            ->whereKey($open->modelKeys())
            ->delete();

        foreach ($schedule as $installment) {
            $this->loan->installments()->create($installment);
        }

        $lastInstallment = array_last($schedule);
        $firstRepayment = array_first(
            array_filter(
                $schedule,
                fn (array $installment): bool => bccomp($installment['principal_amount'], '0', 2) === 1
            )
        );

        $this->loan->fill([
            'installment_amount' => $firstRepayment
                ? bcadd($firstRepayment['principal_amount'], $firstRepayment['interest_amount'], 2)
                : $this->loan->installment_amount,
            'ends_at' => $lastInstallment
                ? $lastInstallment['due_date']
                : $this->loan->installments()->max('due_date'),
        ]);
        $this->loan->calculateRemaining()
            ->calculateTotalInterest()
            ->calculateProgress()
            ->save();

        return $extraRepayment->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('loan_id'))
            ->firstOrFail();

        if (! $this->loan->allows_extra_repayments) {
            throw ValidationException::withMessages([
                'amount' => ['This loan does not allow extra repayments.'],
            ]);
        }

        $executedAt = Carbon::parse($this->getData('executed_at'));
        $amount = bcadd((string) $this->getData('amount'), '0', 2);
        $outstanding = bcadd((string) $this->loan->remaining, '0', 2);
        $remainingAllowance = $this->loan->remainingExtraRepaymentAllowance($executedAt->year);
        $errors = [];

        if ($executedAt->lt($this->loan->starts_at)) {
            $errors['executed_at'][] = 'The extra repayment cannot be executed before the loan starts.';
        }

        if ($this->loan->ends_at?->lt($executedAt)) {
            $errors['executed_at'][] = 'The extra repayment cannot be executed after the loan ends.';
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
