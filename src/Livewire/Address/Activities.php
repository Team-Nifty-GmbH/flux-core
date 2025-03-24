<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use FluxErp\Models\Address;
use Livewire\Attributes\Modelable;

class Activities extends BaseActivities
{
    #[Modelable]
    public int $modelId;

    public string $modelType = Address::class;
}
