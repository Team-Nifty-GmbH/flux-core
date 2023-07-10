<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Product;
use Livewire\Component;

class Statistics extends Component implements UserWidget
{
    public int $salesCount = 0;
    public int $activeCustomersCount = 0;
    public int $activeProductsCount = 0;
    public float $revenue = 0;

    public function mount()
    {
        $this->loadStatistics();
    }

    public function render()
    {
        return view('flux::livewire.widgets.statistics',
            [
                'currency' => Currency::query()->where('is_default', true)->first()->toArray()
            ]
        );
    }

    public function loadStatistics()
    {
        $this->salesCount = Order::query()->whereNotNull('invoice_number')->count();
        $this->activeCustomersCount = Contact::query()->whereHas('orders')->count();
        $this->activeProductsCount = Product::query()->where('is_active', true)->count();
        $this->revenue = round(Order::query()->sum('total_net_price'));
    }

    public static function getLabel(): string
    {
        return __('Statistics');
    }
}
