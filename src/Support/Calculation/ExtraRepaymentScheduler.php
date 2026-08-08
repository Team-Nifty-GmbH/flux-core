<?php

namespace FluxErp\Support\Calculation;

use Carbon\Carbon;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use Illuminate\Support\Collection;

/**
 * Reschedules the open tail of a loan after an extra repayment. Settled
 * installments and their transactions stay untouched, only what is still open
 * is replaced, either by keeping the installment and ending earlier or by
 * keeping the term and lowering the installment.
 */
class ExtraRepaymentScheduler
{
    protected int $scale = 2;

    /**
     * The open installments the extra repayment applies to, in due order.
     */
    public function openInstallments(Loan $loan): Collection
    {
        return $loan->installments()
            ->unsettled()
            ->orderBy('sequence')
            ->get();
    }

    /**
     * The installments replacing the open tail. An empty array means the extra
     * repayment settles the loan.
     */
    public function reschedule(
        Loan $loan,
        string|float|int $amount,
        ScheduleAdjustmentTypeEnum $adjustmentType,
        ?Collection $openInstallments = null,
    ): array {
        $open = $openInstallments ?? $this->openInstallments($loan);
        $first = $open->first();

        if (is_null($first)) {
            return [];
        }

        $balance = bcsub($this->outstanding($open), $this->normalize($amount), $this->scale);

        if (bccomp($balance, '0', $this->scale) !== 1) {
            return [];
        }

        $interval = $loan->installment_interval_enum ?? InstallmentIntervalEnum::Monthly;

        // the generator dues the first installment one interval after the start,
        // so the tail keeps running on the dates the contract already has
        $startsAt = Carbon::parse($first->due_date)
            ->subMonthsNoOverflow($interval->months());

        // an extra repayment during the grace period lowers the interest of the
        // remaining interest only installments, it does not start the repayment
        $gracePeriod = $this->openGracePeriod($open);
        $repaying = $open->slice($gracePeriod)->values();

        if ($repaying->isEmpty()) {
            return [];
        }

        $schedule = app(RepaymentScheduleGenerator::class)->generate(
            amount: $balance,
            interestRate: $loan->interest_rate,
            numberOfInstallments: $this->numberOfInstallments($loan, $repaying, $balance, $adjustmentType),
            repaymentType: $loan->repayment_type_enum,
            startsAt: $startsAt,
            interval: $interval,
            gracePeriodInstallments: $gracePeriod,
            installmentAmount: $this->installmentAmount($loan, $adjustmentType),
        );

        // the tail continues the sequence of the installments already settled
        foreach ($schedule as $index => $installment) {
            $schedule[$index]['sequence'] = $first->sequence + $index;
        }

        return $schedule;
    }

    /**
     * The interest the extra repayment saves and the installments it drops.
     */
    public function savings(array $schedule, Collection $openInstallments): array
    {
        return [
            'interest_saved' => bcsub(
                $this->totalInterest($openInstallments->all()),
                $this->totalInterest($schedule),
                $this->scale
            ),
            'installments_saved' => max($openInstallments->count() - count($schedule), 0),
        ];
    }

    protected function installmentAmount(Loan $loan, ScheduleAdjustmentTypeEnum $adjustmentType): ?string
    {
        // keeping the installment only works where it is constant, a linear
        // schedule keeps its principal share instead and is handled by the count
        if ($adjustmentType !== ScheduleAdjustmentTypeEnum::ShortenTerm
            || $loan->repayment_type_enum !== RepaymentTypeEnum::Annuity
        ) {
            return null;
        }

        return $this->normalize($loan->installment_amount);
    }

    /**
     * The leading open installments that carry interest only, the part of the
     * grace period the loan has not passed yet.
     */
    protected function openGracePeriod(Collection $open): int
    {
        $gracePeriod = 0;

        foreach ($open as $installment) {
            if (bccomp($this->normalize($installment->principal_amount), '0', $this->scale) !== 0) {
                break;
            }

            $gracePeriod++;
        }

        return $gracePeriod;
    }

    protected function normalize(string|float|int|null $value): string
    {
        return bcadd(
            is_float($value) ? sprintf('%.10F', $value) : (string) ($value ?? 0),
            '0',
            $this->scale
        );
    }

    protected function numberOfInstallments(
        Loan $loan,
        Collection $open,
        string $balance,
        ScheduleAdjustmentTypeEnum $adjustmentType,
    ): int {
        if ($adjustmentType === ScheduleAdjustmentTypeEnum::ReduceInstallment) {
            return $open->count();
        }

        if ($loan->repayment_type_enum === RepaymentTypeEnum::Annuity) {
            // the fixed installment ends the schedule as soon as the balance is
            // gone, the open count is only the ceiling
            return $open->count();
        }

        // a linear schedule keeps the principal share it already has
        $principal = $this->normalize($open->first()->principal_amount);

        if (bccomp($principal, '0', $this->scale) !== 1) {
            return $open->count();
        }

        $installments = (int) bcdiv($balance, $principal, 0);

        return bccomp(bcmul((string) $installments, $principal, $this->scale), $balance, $this->scale) === 0
            ? max($installments, 1)
            : $installments + 1;
    }

    protected function outstanding(Collection $open): string
    {
        return $open->reduce(
            fn (string $carry, LoanInstallment $installment): string => bcadd(
                $carry,
                $this->normalize($installment->principal_amount),
                $this->scale
            ),
            '0'
        );
    }

    protected function totalInterest(array $installments): string
    {
        return array_reduce(
            $installments,
            fn (string $carry, $installment): string => bcadd(
                $carry,
                $this->normalize(
                    $installment instanceof LoanInstallment
                        ? $installment->interest_amount
                        : $installment['interest_amount']
                ),
                $this->scale
            ),
            '0'
        );
    }
}
