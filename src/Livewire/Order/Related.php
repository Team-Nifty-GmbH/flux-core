<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Models\Ticket;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Related extends Component
{
    #[Modelable]
    public OrderForm $order;

    public function render(): View|Factory|Application
    {
        $hasDescendants = resolve_static(Order::class, 'query')
            ->where('parent_id', $this->order->id)
            ->exists();

        return view('flux::livewire.order.related', [
            'hasDescendants' => $hasDescendants,
            'hasCreatedOrders' => resolve_static(Order::class, 'query')
                ->where('created_from_id', $this->order->id)
                ->exists(),
            'hasProjects' => resolve_static(Project::class, 'query')
                ->where('order_id', $this->order->id)
                ->exists(),
            'hasTickets' => resolve_static(Ticket::class, 'query')
                ->where('model_id', $this->order->id)
                ->where('model_type', morph_alias(Order::class))
                ->exists(),
        ]);
    }
}
