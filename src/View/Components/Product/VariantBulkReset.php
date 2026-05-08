<?php

namespace FluxErp\View\Components\Product;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VariantBulkReset extends Component
{
    public function __construct(
        public array $counters = [],
    ) {}

    public function render(): View
    {
        return view('flux::components.product.variant-bulk-reset');
    }
}
