<?php

namespace FluxErp\Livewire\Contact\Accounting;

use FluxErp\Livewire\Forms\ContactBankConnectionForm;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;

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

    protected ?string $includeBefore = 'flux::livewire.contact.accounting.credit-accounts';

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

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('contact_id', $this->contactId)
            ->where('is_credit_account', true);
    }
}
