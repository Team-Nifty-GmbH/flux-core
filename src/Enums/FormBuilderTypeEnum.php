<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum FormBuilderTypeEnum: string
{
    use EnumTrait;

    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Date = 'date';
    case Time = 'time';
    case Datetime = 'datetime';
    case Number = 'number';
    case Password = 'password';
    case Range = 'range';
}
