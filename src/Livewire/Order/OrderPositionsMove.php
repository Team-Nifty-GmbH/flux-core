<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class OrderPositionsMove extends Component
{
    #[Modelable]
    public OrderForm $form;

    public string $view = 'flux::livewire.order.order-positions-move';

    public function render(): View
    {
        return view('flux::livewire.order.order-positions-move', [
            'orderPositions' => resolve_static(OrderPosition::class, 'query')
                ->where('order_id', $this->form->id)
                ->whereNull('parent_id')
                ->with('children')
                ->get(),
        ]);
    }
}
