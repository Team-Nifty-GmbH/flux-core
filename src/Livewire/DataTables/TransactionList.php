<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Actions\Transaction\UpdateTransaction;
use FluxErp\Livewire\Forms\TransactionForm;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Transaction;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class TransactionList extends BaseDataTable
{
    use HasEloquentListeners;

    protected string $model = Transaction::class;

    protected ?string $includeBefore = 'flux::livewire.transactions.transactions';

    public TransactionForm $transactionForm;

    public array $enabledCols = [
        'bank_connection.name',
        'value_date',
        'amount',
        'counterpart_name',
        'purpose',
        'order.invoice_number',
    ];

    public array $formatters = [
        'amount' => 'coloredMoney',
    ];

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Add'))
                ->color('primary')
                ->wireClick('editTransaction')
                ->when(fn () => resolve_static(CreateTransaction::class, 'canPerformAction', [false])),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'bankConnections' => app(BankConnection::class)->query()->get(['bank_connections.id', 'name', 'iban']),
            ]
        );
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->color('primary')
                ->wireClick('editTransaction(record.id)')
                ->when(fn () => resolve_static(UpdateTransaction::class, 'canPerformAction', [false])),
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

        $this->js(<<<'JS'
            $openModal('transaction-details');
        JS);
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

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function deleteTransaction(): bool
    {
        try {
            $this->transactionForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
