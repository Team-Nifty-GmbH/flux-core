<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\OrderTransaction\UpdateOrderTransaction;
use FluxErp\Livewire\Forms\OrderTransactionForm;
use FluxErp\Livewire\Forms\TransactionForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Transaction;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\UnauthorizedException;

class TransactionAssignments extends Component
{
    use Actions, WithPagination;

    public ?array $bankAccounts = null;

    public OrderTransactionForm $orderTransactionForm;

    public int $perPage = 15;

    public ?array $range = null;

    public ?string $search = null;

    public ?int $suggestionCount = null;

    #[Url]
    public ?string $tab = null;

    public TransactionForm $transactionForm;

    public ?int $unassignedCount = null;

    public function mount(): void
    {
        $this->tab ??= __('All');
        $this->updateCounts();
    }

    public function render(): View
    {
        return view('flux::livewire.accounting.transaction-assignments', [
            'bankConnections' => resolve_static(BankConnection::class, 'query')
                ->where('is_active', true)
                ->get(['id', 'name', 'iban'])
                ->toArray(),
        ]);
    }

    #[Renderless]
    public function acceptAll(?Transaction $transaction = null): void
    {
        if (! $transaction?->exists) {
            $suggestions = resolve_static(OrderTransaction::class, 'query')
                ->whereHas('transaction')
                ->where('is_accepted', false)
                ->get();
        } else {
            $suggestions = $transaction->orderTransactions()
                ->where('is_accepted', false)
                ->get();
        }

        $suggestions
            ->map(fn (OrderTransaction $orderTransaction) => $orderTransaction->setAttribute('is_accepted', true))
            ->each(function (OrderTransaction $suggestion): void {
                try {
                    UpdateOrderTransaction::make($suggestion->toArray())
                        ->validate()
                        ->execute();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);
                }
            });

        $this->toast()
            ->success(__('Accepted :count assignments', ['count' => $suggestions->count()]))
            ->send();
        $this->refreshTransactions();
    }

    #[Renderless]
    public function assignOrders(array $orders): void
    {
        resolve_static(Order::class, 'query')
            ->whereKey($orders)
            ->get(['id', 'balance'])
            ->each(function (Order $order): void {
                try {
                    CreateOrderTransaction::make([
                        'transaction_id' => $this->transactionForm->id,
                        'order_id' => $order->getKey(),
                        'amount' => $order->balance,
                        'is_accepted' => true,
                    ])
                        ->validate()
                        ->execute();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);
                }
            });

        $this->refreshTransactions();
        $this->js(<<<'JS'
            $modalClose('transaction-assign-orders-modal');
        JS);
    }

    #[Renderless]
    public function assignOrdersModal(Transaction $transaction): void
    {
        $this->transactionForm->reset();
        $this->transactionForm->fill($transaction);

        $this->js(<<<'JS'
            $modalOpen('transaction-assign-orders-modal');
        JS);
    }

    #[Renderless]
    public function deleteOrderTransaction(OrderTransaction $orderTransaction): void
    {
        $this->orderTransactionForm->reset();
        $this->orderTransactionForm->fill($orderTransaction);

        try {
            $this->orderTransactionForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->refreshTransactions();
    }

    #[Renderless]
    public function editOrderTransaction(OrderTransaction $orderTransaction): void
    {
        $this->orderTransactionForm->reset();
        $this->orderTransactionForm->fill($orderTransaction);

        $this->js(<<<'JS'
            $modalOpen('order-transaction-modal');
        JS);
    }

    #[Renderless]
    public function loadTransactions(): array
    {
        $query = resolve_static(
            Transaction::class,
            $this->search ? 'search' : 'query',
            $this->search ? ['query' => $this->search] : []
        );

        $query = $this->search ? $query->toEloquentBuilder(perPage: 1000) : $query;

        return $query
            ->whereNull('contact_bank_connection_id')
            ->when(
                $this->tab === __('Assignment suggestions'),
                fn (Builder $query) => $query->whereHas(
                    'orders',
                    fn (Builder $query) => $query->where('order_transaction.is_accepted', false)
                )
                    ->where('is_ignored', false)
            )
            ->when(
                $this->tab === __('Open transactions'),
                fn (Builder $query) => $query->whereNot('balance', 0)
                    ->where('is_ignored', false)
            )
            ->withSum(
                [
                    'orderTransactions as unassigned_amount' => fn ($query) => $query->where('is_accepted', false),
                ],
                'amount'
            )
            ->withCount([
                'orderTransactions',
                'comments',
                'orderTransactions as suggestions' => fn ($query) => $query->where('is_accepted', false),
            ])
            ->when($this->range, function (Builder $query): void {
                $query->whereBetween('booking_date', $this->range);
            })
            ->when($this->bankAccounts, function (Builder $query): void {
                $query->whereHas('bankConnection', function (Builder $query): void {
                    $query->whereKey($this->bankAccounts);
                });
            })
            ->latest('booking_date')
            ->with([
                'orders' => function (BelongsToMany $query): void {
                    $query->select([
                        'id',
                        'contact_id',
                        'address_invoice_id',
                        'invoice_number',
                        'invoice_date',
                        'total_gross_price',
                    ])
                        ->withPivot('pivot_id', 'is_accepted', 'amount');
                },
                'orders.addressInvoice:id,name',
                'bankConnection:id,name,bank_name,iban',
                'orders.contact:id',
            ])
            ->paginate()
            ->through(function (Transaction $transaction) {
                $avatarUrl = $transaction->contact?->getAvatarUrl()
                    ?? resolve_static(Contact::class, 'query')
                        ->whereRelation('contactBankConnections', 'iban', $transaction->counterpart_iban)
                        ->first(['id'])
                        ?->getAvatarUrl();
                $transaction->setAttribute('avatar_url', $avatarUrl);

                return $transaction->toArray();
            })
            ->toArray();
    }

    #[Renderless]
    public function saveOrderTransaction(): void
    {
        try {
            $this->orderTransactionForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->refreshTransactions();
        $this->js(<<<'JS'
            $modalClose('order-transaction-modal');
        JS);
    }

    #[Renderless]
    public function showComments(Transaction $transaction): void
    {
        $this->transactionForm->reset();
        $this->transactionForm->fill($transaction);

        $this->js(<<<'JS'
            $modalOpen('transaction-comments-modal');
        JS);
    }

    #[Renderless]
    public function toggleIgnoreTransaction(Transaction $transaction): void
    {
        $this->transactionForm->reset();
        $this->transactionForm->fill($transaction);
        $this->transactionForm->is_ignored = ! $transaction->is_ignored;

        try {
            $this->transactionForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->refreshTransactions();
    }

    public function updatedBankAccounts(): void
    {
        $this->resetPage();
        $this->refreshTransactions();
    }

    public function updatedPage(): void
    {
        $this->refreshTransactions();
    }

    public function updatedRange(): void
    {
        $this->resetPage();
        $this->refreshTransactions();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->refreshTransactions();
    }

    public function updatedTab(): void
    {
        $this->reset('search', 'range', 'bankAccounts');
        $this->resetPage();
        $this->refreshTransactions();
    }

    protected function refreshTransactions(bool $renderless = true): void
    {
        $this->updateCounts();

        if ($renderless) {
            $this->skipRender();
        }

        $this->dispatch('refresh-transactions');
    }

    protected function updateCounts(): void
    {
        $this->suggestionCount = resolve_static(Transaction::class, 'query')
            ->whereHas('orders', fn (Builder $query) => $query->where('order_transaction.is_accepted', false))
            ->count();

        $this->unassignedCount = resolve_static(Transaction::class, 'query')
            ->whereNull('contact_bank_connection_id')
            ->where('is_ignored', false)
            ->whereDoesntHave('orders')
            ->count();
    }
}
