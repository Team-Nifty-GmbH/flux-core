<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Enums\Traits\HasBadge;

enum PrintJobStatusEnum: string
{
    use EnumTrait, HasBadge;

    case Queued = 'queued';

    case Processing = 'processing';

    case Completed = 'completed';

    case Failed = 'failed';

    case Cancelled = 'cancelled';

    public function color(): string
    {
        return match ($this) {
            self::Queued, self::Cancelled => 'gray',
            self::Processing => 'amber',
            self::Completed => 'emerald',
            self::Failed => 'red',
        };
    }
}
