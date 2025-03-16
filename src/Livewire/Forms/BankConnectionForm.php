<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\BankConnection\CreateBankConnection;
use FluxErp\Actions\BankConnection\DeleteBankConnection;
use FluxErp\Actions\BankConnection\UpdateBankConnection;
use Livewire\Attributes\Locked;

class BankConnectionForm extends FluxForm
{
    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public ?int $credit_limit = null;

    public ?int $currency_id = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?int $ledger_account_id = null;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateBankConnection::class,
            'update' => UpdateBankConnection::class,
            'delete' => DeleteBankConnection::class,
        ];
    }
}
