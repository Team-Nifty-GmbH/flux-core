<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\BankConnection\CreateBankConnection;
use FluxErp\Actions\BankConnection\UpdateBankConnection;
use Livewire\Attributes\Locked;
use Livewire\Form;

class BankConnectionForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $currency_id = null;

    public ?int $ledger_account_id = null;

    public ?string $name = null;

    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $iban = null;

    public ?string $bic = null;

    public ?int $credit_limit = null;

    public bool $is_active = true;

    public function save(): void
    {
        $action = $this->id
            ? UpdateBankConnection::make($this->toArray())
            : CreateBankConnection::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
