<?php

namespace FluxErp\Http\Livewire\Order;

use Livewire\Component;

class Comments extends Component
{
    public int $orderId;

    public function mount(int $id)
    {
        $this->orderId = $id;
    }

    public function render()
    {
        return view('flux::livewire.order.comments');
    }
}
