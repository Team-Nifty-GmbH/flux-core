<?php

namespace FluxErp\Enums;

use FluxErp\Support\Calculation\Rounding;

enum GrowthRateTypeEnum: string
{
    case Percentage = 'percentage';

    case Value = 'value';

    public function getValue(string|int|float|null $previousValue, string|int|float|null $currentValue): string
    {
        $previousValue ??= 0;
        $currentValue ??= 0;

        $value = match ($this) {
            GrowthRateTypeEnum::Percentage => bccomp($previousValue, 0) === 0 ?
                (bccomp($currentValue, 0) === 0 ? 0 : bcmul(100, bccomp($currentValue, 0))) :
                bcmul(bcdiv(bcsub($currentValue, $previousValue), $previousValue, 9), 100, 2),
            GrowthRateTypeEnum::Value => bcsub($currentValue, $previousValue, 9),
        };

        return Rounding::round($value);
    }
}