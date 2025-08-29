<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum AbsenceRequestCreationTypeEnum: string
{
    use EnumTrait;

    case Approval_required = 'approval_required';

    case No = 'no';

    case Yes = 'yes';
}
