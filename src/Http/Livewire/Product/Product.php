<?php

namespace FluxErp\Http\Livewire\Product;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Product extends Component
{
    public array $product;

    public function mount(int $id): void
    {
        $this->product = \FluxErp\Models\Product::query()
            ->whereKey($id)
            ->first()
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.product.product');
    }
}
