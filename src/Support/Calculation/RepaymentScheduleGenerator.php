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

    /**
     * The schedule is spaced by the installment interval and interest_rate is
     * the annual rate, so the period rate is rate * months / 12. Grace period
     * installments are prepended as interest only, they carry no principal.
     * An explicit installment amount fixes the payment instead of deriving it,
     * the last installment then settles whatever principal is left.
     */
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
        // the rate keeps its own scale, rounding it to the money scale would
        // collapse anything below a full percent
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

    /**
     * Constant installment via the annuity formula; principal is installment
     * minus interest on the declining balance. A zero rate degenerates to
     * amount / count pure principal.
     */
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

    /**
     * A payment fixed by the contract instead of derived from the term, the
     * common case being a round rate with a smaller stub at the end. The last
     * installment settles the outstanding balance, so it comes out as that stub
     * or, if the payment is too small for the term, as a balloon. A loan repaid
     * before the term ends stops there instead of trailing empty installments.
     */
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

    /**
     * Interest on the balance, rounded to the money scale. Multiplying at the
     * rate scale first keeps the cent from being lost to the repeating decimal
     * of the period rate.
     */
    protected function interest(string $balance, string $periodRate): string
    {
        return bcround(bcmul($balance, $periodRate, $this->rateScale), $this->scale);
    }

    /**
     * Constant principal per period; interest declines with the balance.
     */
    protected function linearPrincipals(string $amount, int $count): array
    {
        $principal = bcdiv($amount, (string) $count, $this->scale);

        return $this->withRemainderOnLast(array_fill(0, $count, $principal), $amount);
    }

    protected function normalize(string|float|int $value, ?int $scale = null): string
    {
        // floats would reach bcadd in scientific notation for very small rates
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
        // a fixed payment only makes sense where the installment is constant,
        // a linear schedule takes its principal from the term instead
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

    /**
     * Force the principals to sum exactly to the loan amount by letting the
     * last installment absorb the rounding remainder.
     */
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
