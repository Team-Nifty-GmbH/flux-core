<?php

namespace FluxErp\Support\Calculation;

use Carbon\Carbon;
use FluxErp\Enums\RepaymentTypeEnum;

class RepaymentScheduleGenerator
{
    // ponytail: installments are spaced monthly and interest_rate is the annual
    // rate, so the period rate is rate / 12. Add a frequency argument here if
    // non-monthly loans ever appear.
    protected int $scale = 2;

    protected int $rateScale = 10;

    public function generate(
        string|float|int $amount,
        string|float|int|null $interestRate,
        int $numberOfInstallments,
        RepaymentTypeEnum $repaymentType,
        Carbon $startsAt,
    ): array {
        $amount = $this->normalize($amount);
        $periodRate = bcdiv($this->normalize($interestRate ?? 0), '12', $this->rateScale);

        $principals = $repaymentType === RepaymentTypeEnum::Annuity
            ? $this->annuityPrincipals($amount, $periodRate, $numberOfInstallments)
            : $this->linearPrincipals($amount, $numberOfInstallments);

        $installments = [];
        $balance = $amount;

        foreach ($principals as $index => $principal) {
            $interest = bcmul($balance, $periodRate, $this->scale);
            $balance = bcsub($balance, $principal, $this->scale);

            $installments[] = [
                'sequence' => $index + 1,
                'due_date' => $startsAt->copy()->addMonthsNoOverflow($index + 1)->toDateString(),
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
        $payment = bcdiv(
            bcmul($amount, bcmul($periodRate, $onePlusRatePowN, $this->rateScale), $this->rateScale),
            bcsub($onePlusRatePowN, '1', $this->rateScale),
            $this->scale
        );

        $principals = [];
        $balance = $amount;

        for ($i = 0; $i < $count; $i++) {
            $interest = bcmul($balance, $periodRate, $this->scale);
            $principal = bcsub($payment, $interest, $this->scale);
            $balance = bcsub($balance, $principal, $this->scale);
            $principals[] = $principal;
        }

        return $this->withRemainderOnLast($principals, $amount);
    }

    /**
     * Constant principal per period; interest declines with the balance.
     */
    protected function linearPrincipals(string $amount, int $count): array
    {
        $principal = bcdiv($amount, (string) $count, $this->scale);

        return $this->withRemainderOnLast(array_fill(0, $count, $principal), $amount);
    }

    protected function normalize(string|float|int $value): string
    {
        return bcadd((string) $value, '0', $this->scale);
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
