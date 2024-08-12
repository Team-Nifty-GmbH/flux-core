<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use Livewire\Attributes\Modelable;

class Activities extends BaseActivities
{
    #[Modelable]
    public int $modelId;

    public string $modelType = \FluxErp\Models\Product::class;
}
