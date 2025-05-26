<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Order;

class Comments extends BaseComments
{
    protected string $modelType = Order::class;
}
