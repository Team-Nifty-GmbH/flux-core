<?php

namespace FluxErp\Support\Calculation;

use Carbon\Carbon;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use Illuminate\Support\Collection;

class ExtraRepaymentScheduler
{
    protected int $scale = 2;

    public function openInstallments(Loan $loan): Collection
    {
        return $loan->installments()
            ->unsettled()
            ->orderBy('sequence')
            ->get();
    }

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

        $startsAt = Carbon::parse($first->due_date)
            ->subMonthsNoOverflow($interval->months());

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

        foreach ($schedule as $index => $installment) {
            $schedule[$index]['sequence'] = $first->sequence + $index;
        }

        return $schedule;
    }

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
        if ($adjustmentType !== ScheduleAdjustmentTypeEnum::ShortenTerm
            || $loan->repayment_type_enum !== RepaymentTypeEnum::Annuity
        ) {
            return null;
        }

        return $this->normalize($loan->installment_amount);
    }

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
            return $open->count();
        }

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
