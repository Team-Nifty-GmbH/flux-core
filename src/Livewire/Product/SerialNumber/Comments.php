<?php

namespace FluxErp\Livewire\Product\SerialNumber;

use FluxErp\Models\SerialNumber;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = SerialNumber::class;
}
