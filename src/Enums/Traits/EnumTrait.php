<?php

namespace FluxErp\Enums\Traits;

trait EnumTrait
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromName(string $name): static|null
    {
        $values = array_values(
            array_filter(self::cases(), fn ($case) => $case->name === $name)
        );

        return array_shift($values);
    }
}
