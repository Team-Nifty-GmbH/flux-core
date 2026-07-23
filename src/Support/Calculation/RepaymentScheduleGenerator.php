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

    public function generate(
        string|float|int $amount,
        string|float|int|null $interestRate,
        int $numberOfInstallments,
        RepaymentTypeEnum $repaymentType,
        Carbon $startsAt,
    ): array {
        $amount = $this->normalize($amount);
        $periodRate = bcdiv($this->normalize($interestRate ?? 0), '12', 12);

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
     * Constant principal per period; interest declines with the balance.
     */
    protected function linearPrincipals(string $amount, int $n): array
    {
        $principal = bcdiv($amount, (string) $n, $this->scale);

        return $this->withRemainderOnLast(array_fill(0, $n, $principal), $amount);
    }

    /**
     * Constant instalment via the annuity formula; principal is instalment
     * minus interest on the declining balance. A zero rate degenerates to
     * amount / n pure principal.
     */
    protected function annuityPrincipals(string $amount, string $periodRate, int $n): array
    {
        if (bccomp($periodRate, '0', 12) === 0) {
            return $this->linearPrincipals($amount, $n);
        }

        $onePlusRatePowN = bcpow(bcadd('1', $periodRate, 12), (string) $n, 12);
        $payment = bcdiv(
            bcmul($amount, bcmul($periodRate, $onePlusRatePowN, 12), 12),
            bcsub($onePlusRatePowN, '1', 12),
            $this->scale
        );

        $principals = [];
        $balance = $amount;

        for ($i = 0; $i < $n; $i++) {
            $interest = bcmul($balance, $periodRate, $this->scale);
            $principal = bcsub($payment, $interest, $this->scale);
            $balance = bcsub($balance, $principal, $this->scale);
            $principals[] = $principal;
        }

        return $this->withRemainderOnLast($principals, $amount);
    }

    /**
     * Force the principals to sum exactly to the loan amount by letting the
     * last instalment absorb the rounding remainder.
     */
    protected function withRemainderOnLast(array $principals, string $amount): array
    {
        $last = count($principals) - 1;
        $sumWithoutLast = '0';

        for ($i = 0; $i < $last; $i++) {
            $sumWithoutLast = bcadd($sumWithoutLast, $principals[$i], $this->scale);
        }

        $principals[$last] = bcsub($amount, $sumWithoutLast, $this->scale);

        return $principals;
    }

    protected function normalize(string|float|int $value): string
    {
        return bcadd((string) $value, '0', $this->scale);
    }
}
