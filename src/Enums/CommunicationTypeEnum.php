<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class CommunicationTypeEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Mail = 'mail';

    final public const string Letter = 'letter';

    final public const string PhoneCall = 'phone-call';
}
