<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Enums\Traits\HasBadge;

enum AbsenceRequestStateEnum: string
{
    use EnumTrait, HasBadge;

    case Approved = 'approved';

    case Pending = 'pending';

    case Rejected = 'rejected';

    case Revoked = 'revoked';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Approved => 'emerald',
            self::Rejected => 'red',
            self::Revoked => 'amber',
        };
    }
}
