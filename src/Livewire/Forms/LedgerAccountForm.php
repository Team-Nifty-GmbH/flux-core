<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use Livewire\Attributes\Locked;

class LedgerAccountForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $number = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $ledger_account_type_enum = null;

    public bool $is_automatic = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateLedgerAccount::class,
            'update' => UpdateLedgerAccount::class,
            'delete' => DeleteLedgerAccount::class,
        ];
    }
}
