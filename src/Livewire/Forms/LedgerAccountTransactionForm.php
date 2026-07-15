<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LedgerAccountTransaction\CreateLedgerAccountTransaction;
use FluxErp\Actions\LedgerAccountTransaction\DeleteLedgerAccountTransaction;
use FluxErp\Actions\LedgerAccountTransaction\UpdateLedgerAccountTransaction;
use Livewire\Attributes\Locked;

class LedgerAccountTransactionForm extends FluxForm
{
    public ?float $amount = null;

    public bool $is_accepted = true;

    public ?int $ledger_account_id = null;

    public ?string $note = null;

    #[Locked]
    public ?int $pivot_id = null;

    #[Locked]
    public ?int $transaction_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLedgerAccountTransaction::class,
            'update' => UpdateLedgerAccountTransaction::class,
            'delete' => DeleteLedgerAccountTransaction::class,
        ];
    }

    protected function getKey(): string
    {
        return 'pivot_id';
    }
}
