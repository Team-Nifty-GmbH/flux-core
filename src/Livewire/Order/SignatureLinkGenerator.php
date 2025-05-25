<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Models\Order;
use FluxErp\Support\Livewire\SignatureLinkGenerator as BaseSignatureLinkGenerator;

class SignatureLinkGenerator extends BaseSignatureLinkGenerator
{
    protected string $modelType = Order::class;
}
