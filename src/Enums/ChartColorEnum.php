<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class ChartColorEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Amber = '#ff9900';

    final public const string Blue = '#0083ff';

    final public const string Cyan = '#00b9da';

    final public const string Emerald = '#00bb7e';

    final public const string Fuchsia = '#d838fa';

    final public const string Gray = '#687282';

    final public const string Green = '#00c753';

    final public const string Indigo = '#2c64ff';

    final public const string Lime = '#8acd00';

    final public const string Mauve = '#78697b';

    final public const string Mist = '#66787c';

    final public const string Neutral = '#737373';

    final public const string Olive = '#7e7c67';

    final public const string Orange = '#ff6800';

    final public const string Pink = '#f73799';

    final public const string Purple = '#9B4fff';

    final public const string Red = '#ff2d35';

    final public const string Rose = '#ff2455';

    final public const string Sky = '#00a7f4';

    final public const string Slate = '#5d748e';

    final public const string Stone = '#79716b';

    final public const string Taupe = '#7d6d67';

    final public const string Teal = '#00bba7';

    final public const string Violet = '#7459ff';

    final public const string Yellow = '#f8af00';

    final public const string Zinc = '#70717b';

    public static function fromColor(string $colorName): string
    {
        return (static::cases()[ucfirst($colorName)] ?? static::forKey($colorName))->value;
    }

    public static function forIndex(int $index): object
    {
        return array_values(static::cases())[$index % count(static::cases())];
    }

    public static function forKey(string|int $key): object
    {
        return static::forIndex(abs(crc32((string) $key)));
    }
}
