<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use FluxErp\Models\Product;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class Activities extends BaseActivities
{
    #[Modelable]
    public int $modelId;

    #[Locked]
    public string $modelType = Product::class;
}
