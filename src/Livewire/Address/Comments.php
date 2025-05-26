<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Address;

class Comments extends BaseComments
{
    protected string $modelType = Address::class;
}
