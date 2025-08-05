<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Livewire\Forms\ContactBankConnectionForm;
use FluxErp\Livewire\Forms\TransactionForm;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class CreditAccounts extends BankConnections
{
    use Actions;

    public array $columnLabels = [
        'bank_name' => 'Name',
    ];

    public ContactBankConnectionForm $contactBankConnection;

    #[Modelable]
    public int $contactId;

    public array $enabledCols = [
        'bank_name',
        'account_holder',
        'balance',
    ];

    public TransactionForm $transactionForm;

    protected ?string $includeBefore = 'flux::livewire.contact.accounting.credit-accounts';

    protected function getRowActions(): array
    {
        return array_merge(
            [
                DataTableButton::make()
                    ->text(__('Create Transaction'))
                    ->icon('banknotes')
                    ->color('indigo')
                    ->wireClick('createTransaction(record.id)')
                    ->when(resolve_static(CreateTransaction::class, 'canPerformAction', [false])),
            ],
            parent::getRowActions()
        );
    }

    #[Renderless]
    public function createTransaction(int $id): void
    {
        $this->transactionForm->reset();
        $this->transactionForm->contact_bank_connection_id = $id;
        $this->transactionForm->booking_date = now()->format('Y-m-d');
        $this->transactionForm->value_date = now()->format('Y-m-d');

        $this->js(<<<'JS'
            $modalOpen('transaction-details-modal');
        JS);
    }

    public function save(): bool
    {
        $this->contactBankConnection->contact_id = $this->contactId;
        $this->contactBankConnection->is_credit_account = true;

        try {
            $this->contactBankConnection->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function saveTransaction(): bool
    {
        try {
            $this->transactionForm->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId)
            ->where('is_credit_account', true);
    }
}
