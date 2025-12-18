<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum EmployeeCanCreateEnum: string
{
    use EnumTrait;

    case ApprovalRequired = 'approval_required';

    case Yes = 'yes';

    case No = 'no';
}
