<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use FluxErp\Models\Address;
use Livewire\Attributes\Locked;

class Activities extends BaseActivities
{
    #[Locked]
    public ?string $modelType = Address::class;
}
