<?php

namespace FluxErp\Support\Calculation;

use Carbon\Carbon;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use FluxErp\Traits\Makeable;
use Illuminate\Support\Collection;

class ExtraRepaymentScheduler
{
    use Makeable;

    protected ?Collection $openInstallments = null;

    protected int $scale = 2;

    protected array $schedule = [];

    public function __construct(protected Loan $loan) {}

    public function getOpenInstallments(): Collection
    {
        return $this->openInstallments ??= $this->loan->installments()
            ->unsettled()
            ->orderBy('sequence')
            ->get();
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    public function reschedule(string|float|int $amount, ScheduleAdjustmentTypeEnum $adjustmentType): static
    {
        $this->schedule = [];

        $open = $this->getOpenInstallments();
        $first = $open->first();

        if (is_null($first)) {
            return $this;
        }

        $balance = bcsub($this->outstanding($open), $this->normalize($amount), $this->scale);

        if (bccomp($balance, '0', $this->scale) !== 1) {
            return $this;
        }

        $interval = $this->loan->installment_interval_enum ?? InstallmentIntervalEnum::Monthly;

        $startsAt = Carbon::parse($first->due_date)
            ->subMonthsNoOverflow($interval->months());

        $gracePeriod = $this->openGracePeriod($open);
        $repaying = $open->slice($gracePeriod)->values();

        if ($repaying->isEmpty()) {
            return $this;
        }

        $schedule = app(RepaymentScheduleGenerator::class)->generate(
            amount: $balance,
            interestRate: $this->loan->interest_rate,
            numberOfInstallments: $this->numberOfInstallments($repaying, $balance, $adjustmentType),
            repaymentType: $this->loan->repayment_type_enum,
            startsAt: $startsAt,
            interval: $interval,
            gracePeriodInstallments: $gracePeriod,
            installmentAmount: $this->installmentAmount($adjustmentType),
        );

        foreach ($schedule as $index => $installment) {
            $schedule[$index]['sequence'] = $first->sequence + $index;
        }

        $this->schedule = $schedule;

        return $this;
    }

    public function savings(): array
    {
        $open = $this->getOpenInstallments();

        return [
            'interest_saved' => bcsub(
                $this->totalInterest($open->all()),
                $this->totalInterest($this->schedule),
                $this->scale
            ),
            'installments_saved' => max($open->count() - count($this->schedule), 0),
        ];
    }

    protected function installmentAmount(ScheduleAdjustmentTypeEnum $adjustmentType): ?string
    {
        if ($adjustmentType !== ScheduleAdjustmentTypeEnum::ShortenTerm
            || $this->loan->repayment_type_enum !== RepaymentTypeEnum::Annuity
        ) {
            return null;
        }

        return $this->normalize($this->loan->installment_amount);
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
        Collection $open,
        string $balance,
        ScheduleAdjustmentTypeEnum $adjustmentType,
    ): int {
        if ($adjustmentType === ScheduleAdjustmentTypeEnum::ReduceInstallment) {
            return $open->count();
        }

        if ($this->loan->repayment_type_enum === RepaymentTypeEnum::Annuity) {
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
