<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\ResetPaymentReminderLevel;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Livewire\DataTables\OrderTransactionList;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Forms\OrderTransactionForm;
use FluxErp\Livewire\Forms\TransactionForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Transaction;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Accounting extends OrderTransactionList
{
    use DataTableHasFormEdit;

    public array $enabledCols = [
        'amount',
        'transaction.purpose',
        'transaction.value_date',
    ];

    public array $formatters = [
        'amount' => ['coloredMoney', ['property' => 'currency_iso']],
    ];

    public ?string $modelKeyName = 'pivot_id';

    #[Modelable]
    public OrderForm $order;

    #[DataTableForm(modalName: 'order-transaction-modal')]
    public OrderTransactionForm $orderTransactionForm;

    public TransactionForm $transactionForm;

    public ?int $newPaymentReminderLevel = null;

    protected ?string $includeBefore = 'flux::livewire.order.accounting';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Add'))
                ->color('indigo')
                ->wireClick('editTransaction')
                ->when(fn () => resolve_static(CreateTransaction::class, 'canPerformAction', [false])),
        ];
    }

    #[Renderless]
    public function editTransaction(?Transaction $transaction): void
    {
        $this->transactionForm->reset();
        $this->transactionForm->fill($transaction);
        if (! $this->transactionForm->booking_date) {
            $this->transactionForm->booking_date = now()->format('Y-m-d');
        }

        if (! $this->transactionForm->value_date) {
            $this->transactionForm->value_date = now()->format('Y-m-d');
        }
        $this->transactionForm->order_id = $this->order->id;

        $this->js(<<<'JS'
            $modalOpen('transaction-details-modal');
        JS);

        if (! $transaction) {
            $this->transactionForm->amount = $this->order->balance;
        }
    }

    #[Renderless]
    public function resetPaymentReminderLevel(): bool
    {
        try {
            ResetPaymentReminderLevel::make([
                'id' => $this->order->id,
                'payment_reminder_current_level' => $this->newPaymentReminderLevel,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->order->payment_reminder_current_level = $this->newPaymentReminderLevel;
        $this->reset('newPaymentReminderLevel');

        $this->toast()
            ->success(__('Payment Reminder Level set successfully'))
            ->send();

        return true;
    }

    #[Renderless]
    public function saveTransaction(): bool
    {
        try {
            $this->transactionForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

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

        $this->loadData();

        return true;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('order_id', $this->order->id)
            ->with([
                'transaction',
                'transaction.currency:id,iso,symbol',
            ]);
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'currency_iso',
            ]
        );
    }

    protected function getTableActionsDataTableHasFormEdit(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'bankConnections' => resolve_static(BankConnection::class, 'query')
                    ->get(['bank_connections.id', 'name', 'iban']),
            ]
        );
    }

    protected function itemToArray($item): array
    {
        $array = parent::itemToArray($item);
        $array['currency_iso'] = $item->transaction?->currency?->iso;

        return $array;
    }
}
