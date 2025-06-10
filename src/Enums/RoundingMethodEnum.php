<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Calculation\Rounding;

enum RoundingMethodEnum: string
{
    use EnumTrait;

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

    case Ceil = 'ceil';

    case End = 'end';

    case Floor = 'floor';

    case Nearest = 'nearest';

    case None = 'none';

    case Round = 'round';
}
