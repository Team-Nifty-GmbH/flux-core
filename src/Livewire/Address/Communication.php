<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Communications\Communication as BaseCommunication;
use FluxErp\Models\Address;

class Communication extends BaseCommunication
{
    protected ?string $modelType = Address::class;
}
