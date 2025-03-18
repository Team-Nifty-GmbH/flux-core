<?php

namespace FluxErp\Support\Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[Attribute]
class DataTableForm extends LivewireAttribute
{
    public function __construct(public ?string $modalName = null) {}
}
