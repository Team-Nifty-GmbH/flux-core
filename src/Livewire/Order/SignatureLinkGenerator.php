<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Support\SignatureLinkGenerator as BaseSignatureLinkGenerator;
use FluxErp\Models\Order;

class SignatureLinkGenerator extends BaseSignatureLinkGenerator
{
    protected string $modelType = Order::class;
}
