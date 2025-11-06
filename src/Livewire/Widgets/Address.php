<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Address as AddressModel;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Address extends Component
{
    public array $address = [];

    public bool $withoutHeader = false;

    public function mount(int $modelId): void
    {
        $address = resolve_static(AddressModel::class, 'query')
            ->whereKey($modelId)
            ->with([
                'contact.media',
                'contactOptions',
            ])
            ->first();

        $this->address = $address->toArray();
        $this->address['avatar'] = $address->getAvatarUrl();
        $this->address['label'] = $address->getLabel();
        $this->address['description'] = $address->getDescription();

        $this->address['total_net'] = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->where('contact_id', data_get($this->address, 'contact_id'))
            ->sum('total_net_price');
        $this->address['balance'] = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->whereNotState('payment_state', Paid::class)
            ->where('contact_id', data_get($this->address, 'contact_id'))
            ->sum('balance');
        $this->address['total_invoices'] = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->where('contact_id', data_get($this->address, 'contact_id'))
            ->count();
        $this->address['revenue_this_year'] = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->where('contact_id', data_get($this->address, 'contact_id'))
            ->whereYear('invoice_date', now()->year)
            ->sum('total_net_price');
        $this->address['revenue_last_year'] = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_number')
            ->where('contact_id', data_get($this->address, 'contact_id'))
            ->whereYear('invoice_date', now()->subYear()->year)
            ->sum('total_net_price');

        $this->address['orders'] = resolve_static(OrderType::class, 'query')
            ->select(['id', 'name'])
            ->whereHas(
                'orders',
                fn (Builder $query) => $query->where('contact_id', data_get($this->address, 'contact_id'))
            )
            ->withCount([
                'orders' => fn (Builder $query) => $query->where('contact_id', data_get($this->address, 'contact_id')),
            ])
            ->get()
            ->toArray();
    }

    public function render(): Factory|View|Application
    {
        return view('flux::livewire.widgets.address');
    }

    public function placeholder(): View
    {
        return view('flux::livewire.placeholders.box');
    }
}
