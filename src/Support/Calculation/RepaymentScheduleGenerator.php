<?php

namespace FluxErp\Support\Calculation;

use Carbon\Carbon;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use InvalidArgumentException;

class RepaymentScheduleGenerator
{
    protected int $scale = 2;

    protected int $rateScale = 10;

    public function generate(
        string|float|int $amount,
        string|float|int|null $interestRate,
        int $numberOfInstallments,
        RepaymentTypeEnum $repaymentType,
        Carbon $startsAt,
        ?InstallmentIntervalEnum $interval = null,
        int $gracePeriodInstallments = 0,
        string|float|int|null $installmentAmount = null,
    ): array {
        $interval ??= InstallmentIntervalEnum::Monthly;
        $amount = $this->normalize($amount);
        $periodRate = bcdiv(
            bcmul(
                $this->normalize($interestRate ?? 0, $this->rateScale),
                (string) $interval->months(),
                $this->rateScale
            ),
            '12',
            $this->rateScale
        );

        $principals = array_merge(
            array_fill(0, $gracePeriodInstallments, $this->normalize(0)),
            $this->principals($amount, $periodRate, $numberOfInstallments, $repaymentType, $installmentAmount)
        );

        $installments = [];
        $balance = $amount;

        foreach ($principals as $index => $principal) {
            $interest = $this->interest($balance, $periodRate);
            $balance = bcsub($balance, $principal, $this->scale);

            $installments[] = [
                'sequence' => $index + 1,
                'due_date' => $startsAt->copy()
                    ->addMonthsNoOverflow($interval->months() * ($index + 1))
                    ->toDateString(),
                'principal_amount' => $principal,
                'interest_amount' => $interest,
            ];
        }

        return $installments;
    }

    protected function annuityPrincipals(string $amount, string $periodRate, int $count): array
    {
        if (bccomp($periodRate, '0', $this->rateScale) === 0) {
            return $this->linearPrincipals($amount, $count);
        }

        $onePlusRatePowN = bcpow(bcadd('1', $periodRate, $this->rateScale), (string) $count, $this->rateScale);
        $payment = bcround(
            bcdiv(
                bcmul($amount, bcmul($periodRate, $onePlusRatePowN, $this->rateScale), $this->rateScale),
                bcsub($onePlusRatePowN, '1', $this->rateScale),
                $this->rateScale
            ),
            $this->scale
        );

        $principals = [];
        $balance = $amount;

        for ($i = 0; $i < $count; $i++) {
            $interest = $this->interest($balance, $periodRate);
            $principal = bcsub($payment, $interest, $this->scale);
            $balance = bcsub($balance, $principal, $this->scale);
            $principals[] = $principal;
        }

        return $this->withRemainderOnLast($principals, $amount);
    }

    protected function fixedPaymentPrincipals(string $amount, string $periodRate, int $count, string $payment): array
    {
        $principals = [];
        $balance = $amount;

        for ($i = 0; $i < $count; $i++) {
            $interest = $this->interest($balance, $periodRate);
            $principal = bcsub($payment, $interest, $this->scale);

            if (bccomp($principal, '0', $this->scale) !== 1) {
                throw new InvalidArgumentException(
                    'The installment amount does not cover the interest of the loan.'
                );
            }

            if ($i === $count - 1 || bccomp($principal, $balance, $this->scale) === 1) {
                $principal = $balance;
            }

            $principals[] = $principal;
            $balance = bcsub($balance, $principal, $this->scale);

            if (bccomp($balance, '0', $this->scale) === 0) {
                break;
            }
        }

        return $principals;
    }

    protected function interest(string $balance, string $periodRate): string
    {
        return bcround(bcmul($balance, $periodRate, $this->rateScale), $this->scale);
    }

    protected function linearPrincipals(string $amount, int $count): array
    {
        $principal = bcdiv($amount, (string) $count, $this->scale);

        return $this->withRemainderOnLast(array_fill(0, $count, $principal), $amount);
    }

    protected function normalize(string|float|int $value, ?int $scale = null): string
    {
        return bcadd(
            is_float($value) ? sprintf('%.10F', $value) : (string) $value,
            '0',
            $scale ?? $this->scale
        );
    }

    protected function principals(
        string $amount,
        string $periodRate,
        int $count,
        RepaymentTypeEnum $repaymentType,
        string|float|int|null $installmentAmount,
    ): array {
        if (! is_null($installmentAmount) && $repaymentType === RepaymentTypeEnum::Annuity) {
            return $this->fixedPaymentPrincipals(
                $amount,
                $periodRate,
                $count,
                $this->normalize($installmentAmount)
            );
        }

        return $repaymentType === RepaymentTypeEnum::Annuity
            ? $this->annuityPrincipals($amount, $periodRate, $count)
            : $this->linearPrincipals($amount, $count);
    }

    protected function withRemainderOnLast(array $principals, string $amount): array
    {
        $last = array_key_last($principals);
        $sumWithoutLast = '0';

        foreach ($principals as $index => $principal) {
            if ($index === $last) {
                continue;
            }

            $sumWithoutLast = bcadd($sumWithoutLast, $principal, $this->scale);
        }

        $principals[$last] = bcsub($amount, $sumWithoutLast, $this->scale);

        return $principals;
    }
}
