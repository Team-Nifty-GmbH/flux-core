<?php

namespace FluxErp\Http\Livewire\Widgets;

use FluxErp\Contracts\UserWidget;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Product;
use Livewire\Component;

class TotalProfit extends Component implements UserWidget
{
    public function render()
    {
        return view('flux::livewire.widgets.total-profit',
            [
                'currency' => Currency::query()->where('is_default', true)->first()->toArray()
            ]
        );
    }

    public static function getLabel(): string
    {
        return __('Total Profit');
    }
}
