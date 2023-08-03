<?php

namespace FluxErp\Http\Livewire\Order;

use Livewire\Component;

class Attachments extends Component
{
    public int $orderId;

    public function mount(int $id): void
    {
        $this->orderId = $id;
    }

    public function render()
    {
        return view('flux::livewire.order.attachments');
    }
}
