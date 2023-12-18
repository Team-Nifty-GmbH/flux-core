<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Accounting extends Component
{
    #[Modelable]
    public OrderForm $order;

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.order.accounting');
    }
}
