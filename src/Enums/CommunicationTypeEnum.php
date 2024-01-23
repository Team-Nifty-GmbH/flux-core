<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum CommunicationTypeEnum: string
{
    use EnumTrait;

    case Letter = 'letter';
    case Mail = 'mail';
    case PhoneCall = 'phone-call';
}
