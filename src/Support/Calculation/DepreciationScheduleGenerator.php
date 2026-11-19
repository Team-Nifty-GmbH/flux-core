<?php

namespace FluxErp\Support\Calculation;

use Carbon\Carbon;
use FluxErp\Enums\DepreciationMethodEnum;
use InvalidArgumentException;

class DepreciationScheduleGenerator
{
    protected int $scale = 2;

    public function generate(
        string|float|int $acquisitionCost,
        string|float|int $residualValue,
        int $usefulLifeMonths,
        DepreciationMethodEnum $method,
        Carbon $startsAt,
    ): array {
        $acquisitionCost = $this->normalize($acquisitionCost);
        $depreciable = bcsub($acquisitionCost, $this->normalize($residualValue), $this->scale);

        $amounts = match ($method) {
            DepreciationMethodEnum::Linear => $this->linearAmounts($depreciable, $usefulLifeMonths),
        };

        $firstMonth = $startsAt->copy()->startOfMonth();
        $bookValue = $acquisitionCost;
        $depreciations = [];

        foreach ($amounts as $index => $amount) {
            $bookValue = bcsub($bookValue, $amount, $this->scale);

            $depreciations[] = [
                'sequence' => $index + 1,
                'booking_date' => $firstMonth->copy()
                    ->addMonthsNoOverflow($index)
                    ->endOfMonth()
                    ->toDateString(),
                'amount' => $amount,
                'book_value_after' => $bookValue,
            ];
        }

        return $depreciations;
    }

    protected function linearAmounts(string $depreciable, int $months): array
    {
        $monthly = bcround(bcdiv($depreciable, (string) $months, $this->scale + 1), $this->scale);

        if (bccomp($monthly, '0', $this->scale) !== 1) {
            throw new InvalidArgumentException(
                'The depreciable amount is too small for the useful life.'
            );
        }

        $amounts = [];
        $remaining = $depreciable;

        for ($i = 0; $i < $months && bccomp($remaining, '0', $this->scale) === 1; $i++) {
            $amount = $i === $months - 1 || bccomp($monthly, $remaining, $this->scale) === 1
                ? $remaining
                : $monthly;

            $amounts[] = $amount;
            $remaining = bcsub($remaining, $amount, $this->scale);
        }

        return $amounts;
    }

    protected function normalize(string|float|int $value): string
    {
        return bcadd(
            is_float($value) ? sprintf('%.10F', $value) : (string) $value,
            '0',
            $this->scale
        );
    }
}
