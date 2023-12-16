<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Livewire\Features\Activities as BaseActivities;
use FluxErp\Livewire\Forms\ProductForm;
use Livewire\Attributes\Modelable;

class Activities extends BaseActivities
{
    #[Modelable]
    public ProductForm $product;

    public string $modelType = \FluxErp\Models\Product::class;

    public function mount(): void
    {
        $this->modelId = $this->product->id;
    }
}
