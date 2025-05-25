<?php

namespace FluxErp\Livewire\SignaturePublicLink;

use FluxErp\Models\Order;
use FluxErp\Support\Livewire\Comments as BaseComments;

class Comments extends BaseComments
{
    protected string $modelType = Order::class;
}
