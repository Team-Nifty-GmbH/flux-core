<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class ChartColorEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Amber = '#f59e0b';

    final public const string Blue = '#3b82f6';

    final public const string Cyan = '#06b6d4';

    final public const string Emerald = '#10b981';

    final public const string Fuchsia = '#d946ef';

    final public const string Green = '#22c55e';

    final public const string Indigo = '#6366f1';

    final public const string Lime = '#84cc16';

    final public const string Orange = '#f97316';

    final public const string Pink = '#ec4899';

    final public const string Purple = '#a855f7';

    final public const string Red = '#ef4444';

    final public const string Rose = '#f43f5e';

    final public const string Sky = '#0ea5e9';

    final public const string Slate = '#64748b';

    final public const string Teal = '#14b8a6';

    final public const string Violet = '#8b5cf6';

    final public const string Yellow = '#eab308';

    public static function forIndex(int $index): object
    {
        return static::cases()[$index % count(static::cases())];
    }

    public static function forKey(string|int $key): object
    {
        return static::forIndex(abs(crc32((string) $key)));
    }
}
