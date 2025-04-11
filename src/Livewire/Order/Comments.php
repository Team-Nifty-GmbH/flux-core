<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Comments extends Component
{
    #[Modelable]
    public OrderForm $order;

    public function render(): View
    {
        return view('flux::livewire.order.comments');
    }
}
