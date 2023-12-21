<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Attachments extends Component
{
    #[Modelable]
    public OrderForm $order;

    public function render()
    {
        return view('flux::livewire.order.attachments');
    }
}
