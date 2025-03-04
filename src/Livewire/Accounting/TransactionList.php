<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Actions\Transaction\UpdateTransaction;
use FluxErp\Jobs\Accounting\MatchTransactionsWithOrderJob;
use FluxErp\Livewire\DataTables\TransactionList as BaseTransactionList;
use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TransactionList extends BaseTransactionList
{
    public bool $isSelectable = true;

    public ?string $includeBefore = 'flux::livewire.accounting.transaction-list.include-before';

    #[Renderless]
    public function deleteSelected(): void
    {
        try {
            $this->getSelectedModelsQuery()->pluck('id')->each(function (int $id) {
                DeleteTransaction::make(['id' => $id])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            });
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        $this->loadData();

        $this->reset('selected');
    }

    protected function getTableActions(): array
    {
        return array_merge(
            parent::getTableActions(),
            [
                DataTableButton::make()
                    ->text(__('Show unassigned payments'))
                    ->color('indigo')
                    ->wireClick('showUnassignedPayments'),
            ]
        );
    }

    protected function getRowActions(): array
    {
        return array_merge(
            parent::getRowActions(),
            [
                DataTableButton::make()
                    ->text(__('Show Order'))
                    ->attributes(
                        [
                            'x-on:click' => <<<'JS'
                                $wire.showOrder(record.order_id)
                             JS,
                            'x-cloak',
                            'x-show' => 'record.order_id',
                        ]
                    ),
                DataTableButton::make()
                    ->text(__('Assign'))
                    ->color('indigo')
                    ->attributes(
                        [
                            'wire:click' => 'assign(record.id)',
                            'x-cloak',
                            'x-show' => '!record.order_id',
                        ]
                    )
                    ->when(fn () => resolve_static(UpdateTransaction::class, 'canPerformAction', [false])),
            ]
        );
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Start automatic assignment'))
                ->color('indigo')
                ->wireClick('matchTransactions()')
                ->when(fn () => resolve_static(UpdateTransaction::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->when(fn () => resolve_static(DeleteTransaction::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Transaction')]),
                    'wire:click' => 'deleteSelected()',
                ]),
        ];
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'order_id',
            ],
        );
    }

    #[Renderless]
    public function showOrder(Order $order): void
    {
        $this->redirectRoute(name: 'orders.id', parameters: ['id' => $order->id], navigate: true);
    }

    #[Renderless]
    public function showUnassignedPayments(): void
    {
        session()
            ->put(
                $this->getCacheKey() . '_query',
                SessionFilter::make(
                    $this->getCacheKey(),
                    fn (Builder $query) => $query->whereNull('order_id'),
                    __('Unassigned payments'),
                )
            );

        $this->loadData();
    }

    #[Renderless]
    public function editTransaction(?Transaction $transaction): void
    {
        $transaction?->loadMissing('children.order.contact.invoiceAddress:id,name');

        parent::editTransaction($transaction);
    }

    public function matchTransactions(): void
    {
        foreach (array_chunk($this->selected, 20) as $chunk) {
            MatchTransactionsWithOrderJob::dispatchSync($chunk);
        }

        $this->notification()->success(__('The transactions are being matched with the orders.'))->send();
    }

    #[Renderless]
    public function assign(Transaction $transaction): void
    {
        $this->transactionForm->reset();
        $this->transactionForm->fill($transaction);

        $this->js(<<<'JS'
            $modalOpen('assign-order-modal');
        JS);
    }

    public function assignOrders(array $orderIds): void
    {
        $orders = resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $orderIds)
            ->with('contact.invoiceAddress:id,name')
            ->get(['id', 'invoice_number', 'invoice_date', 'balance', 'contact_id', 'total_gross_price'])
            ->toArray();

        foreach ($orders as $order) {
            if ($this->transactionForm->difference > 0) {
                $amount = min(
                    $this->transactionForm->difference ?? $this->transactionForm->amount,
                    bcround($order['balance'], 2)
                );
            } else {
                $amount = max(
                    $this->transactionForm->difference ?? $this->transactionForm->amount,
                    bcround($order['balance'], 2)
                );
            }

            $this->transactionForm->difference = bcsub(
                $this->transactionForm->difference ?? $this->transactionForm->amount,
                $amount, 2
            );
            $this->transactionForm->children[] = [
                'amount' => $amount,
                'order' => $order,
            ];
        }
    }

    public function recalculateDifference(): void
    {
        foreach ($this->transactionForm->children as $assignedOrder) {
            $this->transactionForm->difference = bcsub(
                $this->transactionForm->amount,
                data_get($assignedOrder, 'amount', 0), 2
            );
        }
    }

    public function saveAssignment(): bool
    {
        if (
            count($this->transactionForm->children) === 1
            && bccomp($this->transactionForm->amount, data_get($this->transactionForm->children, '0.amount')) === 0
        ) {
            $this->transactionForm->order_id = data_get($this->transactionForm->children, '0.order.id');

            return $this->saveTransaction();
        }

        // First remove all deleted children
        $children = resolve_static(Transaction::class, 'query')
            ->whereKey($this->transactionForm->id)
            ->first()
            ->children()
            ->pluck('id');
        $deletedIds = $children->diff(collect($this->transactionForm->children)->pluck('id'));

        foreach ($deletedIds as $deletedId) {
            try {
                DeleteTransaction::make(['id' => $deletedId])->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        foreach ($this->transactionForm->children as $assignedOrder) {
            $action = data_get($assignedOrder, 'id') ? UpdateTransaction::class : CreateTransaction::class;
            try {
                $action::make([
                    'id' => data_get($assignedOrder, 'id'),
                    'bank_connection_id' => $this->transactionForm->bank_connection_id,
                    'currency_id' => $this->transactionForm->currency_id,
                    'parent_id' => $this->transactionForm->id,
                    'purpose' => $this->transactionForm->purpose,
                    'booking_date' => $this->transactionForm->booking_date,
                    'value_date' => $this->transactionForm->value_date,
                    'amount' => data_get($assignedOrder, 'amount', 0),
                    'counterpart_name' => $this->transactionForm->counterpart_name,
                    'counterpart_iban' => $this->transactionForm->counterpart_iban,
                    'counterpart_bic' => $this->transactionForm->counterpart_bic,
                    'counterpart_bank_name' => $this->transactionForm->counterpart_bank_name,
                    'order_id' => data_get($assignedOrder, 'order.id'),
                ])->validate()->execute();

                $this->transactionForm->amount = bcsub(
                    $this->transactionForm->amount,
                    $assignedOrder['amount'], 2
                );
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->transactionForm->save();
        $this->loadData();

        return true;
    }
}
