<?php

namespace FluxErp\Enums;

use FluxErp\Support\Calculation\Rounding;

enum GrowthRateTypeEnum: string
{
    case Percentage = 'percentage';
    case Value = 'value';

    public function getValue(float $previousValue, float $currentValue): float
    {
        $value = match ($this) {
            GrowthRateTypeEnum::Percentage => $previousValue == 0 ?
                ($currentValue == 0 ? 0 : 100 * ($currentValue <=> 0)) :
                (($currentValue - $previousValue) / ($previousValue)) * 100,
            GrowthRateTypeEnum::Value => $currentValue - $previousValue,
        };

        return Rounding::round($value);
    }
}
