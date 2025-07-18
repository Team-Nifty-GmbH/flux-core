<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
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
    public array $enabledCols = [
        'value_date',
        'amount',
        'purpose',
    ];

    #[Modelable]
    public OrderForm $order;

    protected ?string $includeBefore = 'flux::livewire.order.accounting';

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

    #[Renderless]
    public function editTransaction(?Transaction $transaction): void
    {
        parent::editTransaction($transaction);

        $this->transactionForm->order_id = $this->order->id;
        if (! $transaction) {
            $this->transactionForm->amount = $this->order->balance;
        }
    }

    #[Renderless]
    public function saveTransaction(): bool
    {
        $result = parent::saveTransaction();

        if ($result) {
            try {
                CreateOrderTransaction::make([
                    'order_id' => $this->order->id,
                    'transaction_id' => $this->transactionForm->id,
                    'amount' => $this->transactionForm->amount,
                    'is_accepted' => true,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
                $this->deleteTransaction();

                return false;
            }
        }

        $this->loadData();

        return $result;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereHas('orders', fn (Builder $builder) => $builder->whereKey($this->order->id));
    }
}
