<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Order;
use FluxErp\States\Order\DeliveryState\Open;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class OpenDeliveries extends ValueBox implements HasWidgetOptions
{
    public bool $shouldBePositive = false;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $this->sum = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereState('delivery_state', Open::class)
            ->revenue()
            ->count('id');
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
                ->whereState('delivery_state', Open::class)
                ->revenue(),
            __($this->title()),
        )
            ->store();

        $this->redirectRoute('orders.orders', navigate: true);
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateSum',
        ];
    }

    protected function icon(): string
    {
        return 'truck';
    }
}
