<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\ContactBankConnection\DeleteContactBankConnection;
use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use Livewire\Attributes\Locked;

class ContactBankConnectionForm extends FluxForm
{
    public ?string $account_holder = null;

    public ?string $bank_name = null;

    public ?string $bic = null;

    public ?int $contact_id = null;

    public ?string $iban = null;

    #[Locked]
    public ?int $id = null;

    public ?bool $is_credit_account = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateContactBankConnection::class,
            'update' => UpdateContactBankConnection::class,
            'delete' => DeleteContactBankConnection::class,
        ];
    }
}
