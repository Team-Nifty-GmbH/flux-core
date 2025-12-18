<?php

namespace FluxErp\Support\Livewire\Attributes;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[Attribute]
class DataTableForm extends LivewireAttribute
{
    public function __construct(
        public ?string $modalName = null,
        public ?array $only = null,
        public ?array $exclude = null,
        public ?string $saveMethod = null,
        public ?string $deleteMethod = null,
        public ?string $editMethod = null,
    ) {}
}
