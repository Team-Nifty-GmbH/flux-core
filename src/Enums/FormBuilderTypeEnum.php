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
    case Stile = 'stile';

    public function getOptionsForType()
    {
        return match($this) {
            self::Text => [
                'placeholder' => __('Enter text here'),
            ],
            self::Textarea => [
                'placeholder' => __('Enter text here'),
                'rows' => '3',
            ],
            self::Select => [
                'placeholder' => __('Select an option'),
                'options' => [],
            ],
            self::Checkbox => [
                'placeholder' => __('Select an option'),
            ],
            self::Radio => [
                'placeholder' => __('Select an option'),
                'options' => [],
            ],
            self::Date => [
                'placeholder' => __('Select a date'),
                'min' => null,
                'max' => null,
            ],
            self::Time => [
                'placeholder' => __('Select a time'),
                'min' => null,
                'max' => null,
            ],
            self::Datetime => [
                'placeholder' => __('Select a date and time'),
                'min' => null,
                'max' => null,
            ],
            self::Number => [
                'placeholder' => __('Enter a number'),
                'min' => null,
                'max' => null,
            ],
            self::Password => [
                'placeholder' => __('Enter a password'),
                'minlength' => null,
            ],
            self::Range => [
                'placeholder' => __('Select a number'),
                'min' => null,
                'max' => null,
            ],
            self::Stile => [
                'placeholder' => __('Enter style attributes here'),
            ],
        };
    }
}
