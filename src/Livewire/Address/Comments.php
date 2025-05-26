<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Models\Address;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = Address::class;
}
