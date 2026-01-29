<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\OrderTransaction\DeleteOrderTransaction;
use FluxErp\Actions\OrderTransaction\UpdateOrderTransaction;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;
use Livewire\Attributes\Locked;

class OrderTransactionForm extends FluxForm
{
    public ?float $amount = null;

    public ?float $exchange_rate = null;

    public bool $is_accepted = false;

    public ?float $order_currency_amount = null;

    #[Locked]
    public ?int $order_id = null;

    #[ExcludeFromActionData]
    public ?string $orderCurrencyIso = null;

    #[Locked]
    public ?int $pivot_id = null;

    #[Locked]
    public ?int $transaction_id = null;

    public function fill($values): void
    {
        parent::fill($values);

        if ($this->order_id && $this->transaction_id) {
            $orderCurrencyId = resolve_static(Order::class, 'query')
                ->whereKey($this->order_id)
                ->value('currency_id');
            $transactionCurrencyId = resolve_static(Transaction::class, 'query')
                ->whereKey($this->transaction_id)
                ->value('currency_id');

            $this->orderCurrencyIso = $orderCurrencyId !== $transactionCurrencyId
                ? resolve_static(Currency::class, 'query')->whereKey($orderCurrencyId)->value('iso')
                : null;
        } else {
            $this->orderCurrencyIso = null;
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateOrderTransaction::class,
            'update' => UpdateOrderTransaction::class,
            'delete' => DeleteOrderTransaction::class,
        ];
    }

    protected function getKey(): string
    {
        return 'pivot_id';
    }
}
