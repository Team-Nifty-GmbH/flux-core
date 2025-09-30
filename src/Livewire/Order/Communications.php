<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Order;

class Communications extends BaseCommunication
{
    protected ?string $modelType = Order::class;
}
