<?php

namespace FluxErp\Support\Livewire\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InlineEditable
{
    public function __construct() {}
}
