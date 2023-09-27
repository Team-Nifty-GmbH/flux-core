<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Address extends Component
{
    public array $address = [];

    public function mount(int $modelId): void
    {
        $address = \FluxErp\Models\Address::query()
            ->whereKey($modelId)
            ->with([
                'contact.media',
                'contactOptions',
            ])
            ->first()
            ->append(['phone', 'email', 'website']);

        $this->address = $address->toArray();
        $this->address['avatar'] = $address->getAvatarUrl();
        $this->address['label'] = $address->getLabel();
        $this->address['description'] = $address->getDescription();

        $this->address['total_net'] = \FluxErp\Models\Order::query()
            ->whereNotNull('invoice_number')
            ->where('contact_id', $this->address['contact_id'])
            ->sum('total_net_price');
    }

    public function render(): Factory|View|Application
    {
        return view('flux::livewire.widgets.address');
    }
}
