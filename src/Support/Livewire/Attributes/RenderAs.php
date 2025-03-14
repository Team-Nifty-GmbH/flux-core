<?php

namespace FluxErp\Support\Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[Attribute]
class RenderAs extends LivewireAttribute
{
    public const string CHECKBOX = 'Checkbox';

    public const string COLOR = 'Color';

    public const string DATE = 'Date';

    public const string INPUT = 'Input';

    public const string NONE = 'None';

    public const string NUMBER = 'Number';

    public const string PASSWORD = 'Password';

    public const string PIN = 'Pin';

    public const string RADIO = 'Radio';

    public const string RANGE = 'Range';

    public const string SELECT = 'Select.styled';

    public const string SELECT_NATIVE = 'Select.native';

    public const string TAG = 'Tag';

    public const string TEXTAREA = 'Textarea';

    public const string TIME = 'Time';

    public const string TOGGLE = 'Toggle';

    public function __construct(
        public string $type,
        public ?array $options = null
    ) {}
}
