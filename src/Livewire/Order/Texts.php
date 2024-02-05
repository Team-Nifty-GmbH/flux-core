<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Texts extends Component
{
    #[Modelable]
    public OrderForm $order;

    public function render(): Factory|Application|View
    {
        return view('flux::livewire.order.texts');
    }
}
