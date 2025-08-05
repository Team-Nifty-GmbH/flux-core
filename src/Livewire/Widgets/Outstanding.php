<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Accounting\PaymentReminder;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class Outstanding extends ValueBox implements HasWidgetOptions
{
    #[Locked]
    public array $orderTypeIds = [];

    public bool $shouldBePositive = false;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function mount(): void
    {
        $this->orderTypeIds = resolve_static(OrderType::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'order_type_enum'])
            ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase()
                && $orderType->order_type_enum->multiplier() > 0
            )
            ->pluck('id')
            ->toArray();

        parent::mount();
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $symbol = resolve_static(Currency::class, 'default')->symbol;
        $this->subValue = '<span class="text-red-600">'
            . Number::abbreviate(
                $this->getOverdueQuery(resolve_static(Order::class, 'query'))->sum('balance'),
                2
            )
            . ' ' . $symbol . ' ' . __('Overdue')
            . '</span>';
        $this->sum = Number::abbreviate(
            $this->getOutstandingQuery(resolve_static(Order::class, 'query'))->sum('balance'),
            2
        ) . ' ' . $symbol;
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
            fn (Builder $query) => $this->getOutstandingQuery($query),
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
            fn (Builder $query) => $this->getOverdueQuery($query),
            __('Overdue'),
        )
            ->store();

        $this->redirectRoute('accounting.payment-reminders', navigate: true);
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Order::class, 'getBroadcastChannel')
                . ',.OrderLocked' => 'calculateSum',
        ];
    }

    protected function getOutstandingQuery(Builder $builder): Builder
    {
        return $builder
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->whereNotState('payment_state', Paid::class)
            ->revenue();
    }

    protected function getOverdueQuery(Builder $builder): Builder
    {
        return $builder
            ->whereRelation('paymentType', 'is_direct_debit', false)
            ->where('balance', '>', 0)
            ->whereNotState('payment_state', Paid::class)
            ->whereNotNull('invoice_number')
            ->whereNotNull('invoice_date')
            ->whereDate('payment_reminder_next_date', '<=', now()->endOfDay()->toDate())
            ->whereIntegerInRaw(
                'order_type_id',
                $this->orderTypeIds
            );
    }

    protected function icon(): string
    {
        return 'banknotes';
    }
}
