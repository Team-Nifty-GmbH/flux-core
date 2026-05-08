<?php

namespace FluxErp\View\Components\Product;

use FluxErp\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InheritanceIndicator extends Component
{
    public bool $isVariant;

    public bool $inheritanceEnabled;

    public bool $isOverridden;

    public function __construct(
        public ?Product $product,
        public string $field,
        public string $resetMethod = 'resetField',
        ?bool $overridden = null,
    ) {
        $this->isVariant = $product?->isVariant() ?? false;
        $this->inheritanceEnabled = $product?->inheritanceEnabled() ?? false;
        $this->isOverridden = $overridden ?? ($this->isVariant && $product->overrides($field));
    }

    public function render(): View
    {
        return view('flux::components.product.inheritance-indicator');
    }

    public function shouldRenderChrome(): bool
    {
        return $this->isVariant && $this->inheritanceEnabled;
    }
}
