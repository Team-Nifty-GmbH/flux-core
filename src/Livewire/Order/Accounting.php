<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\DataTables\TransactionList;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Accounting extends TransactionList
{
    #[Modelable]
    public OrderForm $order;

    protected string $view = 'flux::livewire.order.accounting';

    public array $enabledCols = [
        'value_date',
        'amount',
        'purpose',
    ];

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->order->id);
    }

    #[Renderless]
    public function editTransaction(?Transaction $transaction): void
    {
        parent::editTransaction($transaction);

        $this->transactionForm->order_id = $this->order->id;
        if (! $transaction) {
            $this->transactionForm->amount = $this->order->balance;
        }
    }

    public function deleteTransaction(): bool
    {
        $this->transactionForm->order_id = null;
        try {
            $this->transactionForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
