<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Calculation\Rounding;

enum RoundingMethodEnum: string
{
    use EnumTrait;

    case None = 'none';

    case Round = 'round';

    case Ceil = 'ceil';

    case Floor = 'floor';

    case Nearest = 'nearest';

    case End = 'end';

    public static function valuesLocalized(): array
    {
        return array_combine(
            self::values(),
            [
                __('Do not round'),
                __('Round'),
                __('Round up'),
                __('Round down'),
                __('Round to nearest multiple'),
                __('Round to number'),
            ]
        );
    }

    public function apply(
        string|float|int $value,
        ?int $precision = null,
        ?int $roundingNumber = null,
        ?string $roundingMode = null
    ): string {
        return match ($this) {
            self::None => $value,
            self::Round => Rounding::round($value, $precision),
            self::Ceil => Rounding::ceil($value, $precision),
            self::Floor => Rounding::floor($value, $precision),
            self::Nearest => Rounding::nearest($roundingNumber, $value, $precision, $roundingMode),
            self::End => Rounding::nearest($roundingNumber, $value, $precision, $roundingMode ?? 'ceil'),
        };
    }
}
