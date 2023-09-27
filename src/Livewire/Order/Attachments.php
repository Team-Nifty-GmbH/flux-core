<?php

namespace FluxErp\Livewire\Order;

use Livewire\Component;

class Attachments extends Component
{
    public int $orderId;

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function render()
    {
        return view('flux::livewire.order.attachments');
    }
}
