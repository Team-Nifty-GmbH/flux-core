<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Accounting\PaymentReminder;
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

        $overDueQuery = resolve_static(Order::class, 'query')
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereNotState('payment_state', Paid::class)
            ->where('payment_reminder_next_date', '<=', now()->toDate())
            ->revenue();

        $symbol = Currency::default()->symbol;
        $this->subValue = '<span class="text-negative-600">'
            . Number::abbreviate($overDueQuery->sum('balance'), 2)
            . ' ' . $symbol . __('Overdue')
            . '</span>';
        $this->sum = Number::abbreviate($metric, 2) . ' ' . $symbol;
    }

    public function options(): array
    {
        return [
            [
                'label' => __('Show'),
                'method' => 'show',
            ],
            [
                'label' => __('Show overdue'),
                'method' => 'showOverdue',
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

    #[Renderless]
    public function showOverdue(): void
    {
        SessionFilter::make(
            Livewire::new(resolve_static(PaymentReminder::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->whereNotState('payment_state', Paid::class)
                ->where('payment_reminder_next_date', '<=', now()->toDate())
                ->revenue(),
            __('Overdue'),
        )
            ->store();

        $this->redirectRoute('accounting.payment-reminders', navigate: true);
    }
}
