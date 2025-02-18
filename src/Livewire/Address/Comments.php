<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Address;

class Comments extends BaseComments
{
    public string $modelType = Address::class;
}
