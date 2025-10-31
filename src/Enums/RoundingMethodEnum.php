<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Support\Enums\FluxEnum;

class RoundingMethodEnum extends FluxEnum
{
    use EnumTrait;

    final public const string None = 'none';

    final public const string Round = 'round';

    final public const string Ceil = 'ceil';

    final public const string Floor = 'floor';

    final public const string Nearest = 'nearest';

    final public const string End = 'end';

    public static function apply(
        string $case,
        string|float|int $value,
        ?int $precision = null,
        ?int $roundingNumber = null,
        ?string $roundingMode = null
    ): string {
        return match ($case) {
            static::None => $value,
            static::Round => Rounding::round($value, $precision),
            static::Ceil => Rounding::ceil($value, $precision),
            static::Floor => Rounding::floor($value, $precision),
            static::Nearest => Rounding::nearest($roundingNumber, $value, $precision, $roundingMode),
            static::End => Rounding::nearest($roundingNumber, $value, $precision, $roundingMode ?? 'ceil'),
        };
    }
}
