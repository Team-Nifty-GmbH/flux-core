<?php

namespace FluxErp\View\Components\Product;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InheritanceOrphanBanner extends Component
{
    public function __construct(
        public bool $visible = false,
    ) {}

    public function render(): View
    {
        return view('flux::components.product.inheritance-orphan-banner');
    }
}
