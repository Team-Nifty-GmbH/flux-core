<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ChartColorEnum: string
{
    use EnumTrait;

    public static function forIndex(int $index): self
    {
        return self::cases()[$index % count(self::cases())];
    }

    public static function forKey(string|int $key): self
    {

        return self::forIndex(abs(crc32((string) $key)));
    }

    case Amber = '#f59e0b';

    case Blue = '#3b82f6';

    case Cyan = '#06b6d4';

    case Emerald = '#10b981';

    case Fuchsia = '#d946ef';

    case Green = '#22c55e';

    case Indigo = '#6366f1';

    case Lime = '#84cc16';

    case Orange = '#f97316';

    case Pink = '#ec4899';

    case Purple = '#a855f7';

    case Red = '#ef4444';

    case Rose = '#f43f5e';

    case Sky = '#0ea5e9';

    case Slate = '#64748b';

    case Teal = '#14b8a6';

    case Violet = '#8b5cf6';

    case Yellow = '#eab308';
}
