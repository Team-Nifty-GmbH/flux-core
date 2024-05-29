<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Models\Order;
use Livewire\Component;

class PublicLink extends Component
{
    use WithFileUploads;

    public Order $order;

    public function render()
    {
        return view('flux::livewire.order.public-link');
    }
}
