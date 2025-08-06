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
        return self::forIndex(crc32((string) $key));
    }

    public function hex(): string
    {
        return match ($this) {
            self::Red => '#ef4444',
            self::Orange => '#f97316',
            self::Amber => '#f59e0b',
            self::Yellow => '#eab308',
            self::Lime => '#84cc16',
            self::Green => '#22c55e',
            self::Emerald => '#10b981',
            self::Teal => '#14b8a6',
            self::Cyan => '#06b6d4',
            self::Sky => '#0ea5e9',
            self::Blue => '#3b82f6',
            self::Indigo => '#6366f1',
            self::Violet => '#8b5cf6',
            self::Purple => '#a855f7',
            self::Fuchsia => '#d946ef',
            self::Pink => '#ec4899',
            self::Rose => '#f43f5e',
            self::Slate => '#64748b',
        };
    }

    public function tailwind(): string
    {
        return $this->value;
    }

    case Amber = 'bg-amber-500';

    case Blue = 'bg-blue-500';

    case Cyan = 'bg-cyan-500';

    case Emerald = 'bg-emerald-500';

    case Fuchsia = 'bg-fuchsia-500';

    case Green = 'bg-green-500';

    case Indigo = 'bg-indigo-500';

    case Lime = 'bg-lime-500';

    case Orange = 'bg-orange-500';

    case Pink = 'bg-pink-500';

    case Purple = 'bg-purple-500';

    case Red = 'bg-red-500';

    case Rose = 'bg-rose-500';

    case Sky = 'bg-sky-500';

    case Slate = 'bg-slate-500';

    case Teal = 'bg-teal-500';

    case Violet = 'bg-violet-500';

    case Yellow = 'bg-yellow-500';
}
