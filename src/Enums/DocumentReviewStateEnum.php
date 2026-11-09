<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum DocumentReviewStateEnum: string
{
    use EnumTrait;

    case Pending = 'pending';

    case Approved = 'approved';

    case Rejected = 'rejected';
}
