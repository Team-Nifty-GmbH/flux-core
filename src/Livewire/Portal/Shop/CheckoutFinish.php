<?php

namespace FluxErp\Livewire\Portal\Shop;

use Illuminate\View\View;
use Livewire\Component;

class CheckoutFinish extends Component
{
    public function render(): View
    {
        $this->dispatch('cart:refresh');

        return view('flux::livewire.portal.shop.checkout-finish');
    }
}
