<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class AssignableOrders extends Component
{
    #[Locked]
    public ?int $contactId = null;

    #[Locked]
    public bool $hasDeviation = false;

    #[Locked]
    public ?float $invoiceTotal = null;

    public ?int $orderId = null;

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.accounting.assignable-orders');
    }

    #[On('assignable-orders.load')]
    public function load(?int $contactId = null, float|int|string|null $invoiceTotal = null): void
    {
        $this->reset();

        unset($this->orders);

        if (! resolve_static(CreateOrderFromPurchaseInvoice::class, 'canPerformAction', [false])) {
            return;
        }

        $this->contactId = $contactId;
        $this->invoiceTotal = is_null($invoiceTotal) ? null : (float) $invoiceTotal;

        if ($orderId = $this->suggestedOrderId()) {
            $this->orderId = $orderId;
            $this->updatedOrderId($orderId);
        }
    }

    public function updatedOrderId(mixed $value): void
    {
        $orderId = $value ? (int) $value : null;
        $selectedTotal = is_null($orderId) ? null : $this->selectedTotal($orderId);

        $this->hasDeviation = ! is_null($selectedTotal)
            && ! is_null($this->invoiceTotal)
            && bccomp(
                bcround($selectedTotal, 2),
                bcround((string) $this->invoiceTotal, 2),
                2
            ) !== 0;

        $this->dispatch('assignable-orders.selected', orderId: $orderId);
    }

    #[Computed]
    public function orders(): array
    {
        if (is_null($this->contactId)) {
            return [];
        }

        $groups = resolve_static(Order::class, 'query')
            ->where('contact_id', $this->contactId)
            ->whereNull('invoice_number')
            ->where('is_locked', false)
            ->whereHas(
                'orderType',
                fn (Builder $query) => $query->whereIn(
                    'order_type_enum',
                    collect(OrderTypeEnum::cases())
                        ->filter(fn (OrderTypeEnum $case) => $case->isPurchase())
                        ->map(fn (OrderTypeEnum $case) => $case->value)
                )
            )
            ->with(['createdFrom.orderType:id,order_type_enum', 'currency:id,iso'])
            ->latest('id')
            ->get(['id', 'created_from_id', 'currency_id', 'order_number', 'order_date', 'total_gross_price'])
            ->groupBy(
                fn (Order $order) => $order->createdFrom?->orderType?->order_type_enum?->isSubscription()
                    ? 'rates'
                    : 'orders'
            );

        return $groups
            ->map(fn (Collection $orders, string $group) => [
                'key' => $group,
                'label' => $group === 'rates' ? __('Subscription Rates') : __('Orders'),
                'value' => $orders
                    ->map(fn (Order $order) => [
                        'label' => trim($order->order_number . ' ' . $order->getLabel()),
                        'description' => Number::currency(
                            abs((float) $order->total_gross_price),
                            $order->currency?->iso ?? resolve_static(Currency::class, 'default')?->iso ?? 'EUR',
                            app()->getLocale()
                        ),
                        'value' => $order->getKey(),
                        'total_gross_price' => abs((float) $order->total_gross_price),
                    ])
                    ->values()
                    ->all(),
            ])
            ->sortBy(fn (array $group, string $key) => $key === 'rates' ? 0 : 1)
            ->values()
            ->all();
    }

    protected function selectedTotal(int $orderId): ?string
    {
        foreach ($this->orders as $group) {
            foreach ($group['value'] as $option) {
                if ($option['value'] === $orderId) {
                    return (string) $option['total_gross_price'];
                }
            }
        }

        return null;
    }

    protected function suggestedOrderId(): ?int
    {
        if (is_null($this->invoiceTotal)) {
            return null;
        }

        $matching = collect(
            data_get(collect($this->orders)->firstWhere('key', 'rates'), 'value', [])
        )
            ->filter(
                fn (array $option) => bccomp(
                    bcround((string) data_get($option, 'total_gross_price'), 2),
                    bcround((string) $this->invoiceTotal, 2),
                    2
                ) === 0
            );

        return $matching->count() === 1 ? data_get($matching->first(), 'value') : null;
    }
}
