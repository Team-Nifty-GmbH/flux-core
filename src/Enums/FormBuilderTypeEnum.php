<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum FormBuilderTypeEnum: string
{
    use EnumTrait;
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Datetime = 'datetime';
    case Email = 'email';
    case Number = 'number';
    case Password = 'password';
    case Radio = 'radio';
    case Range = 'range';
    case Select = 'select';

    case Text = 'text';
    case Textarea = 'textarea';
    case Time = 'time';
}
