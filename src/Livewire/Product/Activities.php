<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use FluxErp\Models\Product;
use Livewire\Attributes\Locked;

class Activities extends BaseActivities
{
    #[Locked]
    public ?string $modelType = Product::class;
}
