<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\Address;
use Livewire\Attributes\Locked;

class Comments extends BaseComments
{
    #[Locked]
    public string $modelType = Address::class;
}
