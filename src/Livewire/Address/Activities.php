<?php

namespace FluxErp\Livewire\Address;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use Livewire\Attributes\Modelable;

class Activities extends BaseActivities
{
    public string $modelType = \FluxErp\Models\Address::class;

    #[Modelable]
    public int $modelId;
}
