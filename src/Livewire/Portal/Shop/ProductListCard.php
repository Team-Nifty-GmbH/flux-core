<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Livewire\Forms\Portal\ProductForm;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Lazy]
class ProductListCard extends Component
{
    public ProductForm $productForm;

    public function mount(array $product): void
    {
        $this->productForm->fill($product);
    }

    public function render(): View
    {
        return view('flux::livewire.portal.shop.product-list-card');
    }

    #[Renderless]
    public function addToCart(): void
    {
        $this->dispatch('cart:add', $this->productForm->toArray())->to('portal.shop.cart');
        $this->productForm->amount = 1;
    }
}
