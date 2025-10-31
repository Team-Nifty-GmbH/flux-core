<?php

namespace FluxErp\Enums\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait EnumTrait
{
    public static function toArray(): array
    {
        return Arr::mapWithKeys(
            array_column(static::cases(), 'value'),
            fn ($value) => [$value => __(Str::headline($value))],
        );
    }

    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }

    public static function valuesLocalized(): array
    {
        return array_map(
            fn ($case) => [
                'value' => $case->value,
                'label' => __(Str::headline($case->value)),
            ],
            static::cases()
        );
    }
}
