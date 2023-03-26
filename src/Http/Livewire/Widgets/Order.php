<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Models\OrderPosition;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Order extends Component
{
    public array $order = [];

    public array $orderPositions = [];

    public function mount(int $modelId): void
    {
        $order = \FluxErp\Models\Order::query()
            ->whereKey($modelId)
            ->with([
                'contact.media',
                'currency:id,iso',
                'orderType:id,name',
            ])
            ->first();

        $this->order = $order->toArray();
        $this->order['avatar'] = $order->contact?->getAvatarUrl();
        $this->order['address_invoice']['label'] = $order->address_invoice?->getLabel();
    }

    public function render(): Factory|View|Application
    {
        return view('flux::livewire.widgets.order');
    }

    public function loadOrderPositions(): void
    {
        $positions = OrderPosition::query()
            ->where('order_id', $this->order['id'])
            ->whereNull('parent_id')
            ->with('currency')
            ->get()
            ->append('children')
            ->toArray();

        $this->orderPositions = to_flat_tree($positions);
    }
}
