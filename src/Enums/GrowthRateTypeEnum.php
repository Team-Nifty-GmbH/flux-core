<?php

namespace FluxErp\Enums;

use FluxErp\Support\Calculation\Rounding;

enum GrowthRateTypeEnum: string
{
    public function getValue(string|int|float|null $previousValue, string|int|float|null $currentValue): string
    {
        $previousValue ??= 0;
        $currentValue ??= 0;

        $value = match ($this) {
            GrowthRateTypeEnum::Percentage => bccomp($previousValue, 0) === 0 ?
                (bccomp($currentValue, 0) === 0 ? 0 : bcmul(100, bccomp($currentValue, 0))) :
                bcmul(bcdiv(bcsub($currentValue, $previousValue), $previousValue), 100),
            GrowthRateTypeEnum::Value => bcsub($currentValue, $previousValue),
        };

        return Rounding::round($value);
    }
    case Percentage = 'percentage';

    case Value = 'value';
}
