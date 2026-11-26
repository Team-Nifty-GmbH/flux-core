<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\AccountingSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;

class AccountingSettingsForm extends SettingsForm
{
    #[RenderAs('Toggle')]
    public bool $auto_accept_secure_transaction_matches = false;

    #[RenderAs('Toggle')]
    public bool $auto_send_reminders = false;

    #[RenderAs(
        RenderAs::SELECT,
        options: [
            'unfiltered' => 'true',
            ':request' => "['url' => route('search', \FluxErp\Models\LedgerAccount::class), 'method' => 'POST']",
        ],
        label: 'Clearing Ledger Account'
    )]
    public ?int $clearing_ledger_account_id = null;

    public function getSettingsClass(): string
    {
        return AccountingSettings::class;
    }
}
