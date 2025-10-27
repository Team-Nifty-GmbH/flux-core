<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Livewire\Forms\SettingsForm;
use FluxErp\Settings\AccountingSettings;
use FluxErp\Support\Livewire\Attributes\RenderAs;

class AccountingSettingsForm extends SettingsForm
{
    #[RenderAs('Toggle')]
    public bool $auto_accept_secure_transaction_matches = false;

    #[RenderAs('Toggle')]
    public bool $auto_send_reminders = false;

    public function getSettingsClass(): string
    {
        return AccountingSettings::class;
    }
}
