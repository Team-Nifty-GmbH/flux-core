<?php

namespace FluxErp\Http\Livewire\Order;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Accounting extends Component
{
    public int $orderId;

    public function mount(int $id): void
    {
        $this->orderId = $id;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.order.accounting');
    }
}
