<?php

namespace FluxErp\Livewire\Product\SerialNumber;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;
use FluxErp\Models\SerialNumber;
use Livewire\Attributes\Locked;

class Comments extends BaseComments
{
    #[Locked]
    public string $modelType = SerialNumber::class;
}
