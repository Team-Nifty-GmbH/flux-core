<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\BankConnection\CreateBankConnection;
use FluxErp\Actions\BankConnection\DeleteBankConnection;
use FluxErp\Actions\BankConnection\UpdateBankConnection;

class BankConnectionForm extends FluxForm
{
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

    protected function getActions(): array
    {
        return [
            'create' => CreateBankConnection::class,
            'update' => UpdateBankConnection::class,
            'delete' => DeleteBankConnection::class,
        ];
    }
}
