<?php

namespace FluxErp\Enums\Traits;

trait EnumTrait
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function valuesLocalized(): array
    {
        return array_combine(
            self::values(),
            array_map(fn ($value) => __($value), self::values()),
        );
    }

    public static function fromName(string $name): ?static
    {
        $values = array_values(
            array_filter(self::cases(), fn ($case) => $case->name === $name)
        );

        return array_shift($values);
    }
}
