<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Support\Widgets\ValueBox;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class Outstanding extends ValueBox implements HasWidgetOptions
{
    public bool $shouldBePositive = false;

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateSum',
        ];
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $metric = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereNotState('payment_state', Paid::class)
            ->revenue()
            ->sum('balance');

        $symbol = Currency::default()->symbol;
        $this->sum = Number::abbreviate($metric, 2) . ' ' . $symbol;
    }

    public function options(): array
    {
        return [
            [
                'label' => __('Show'),
                'method' => 'show',
            ],
        ];
    }

    #[Renderless]
    public function show(): void
    {
        SessionFilter::make(
            Livewire::new(resolve_static(OrderList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereNotState('payment_state', Paid::class)
                ->revenue(),
            __($this->title()),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }
}
