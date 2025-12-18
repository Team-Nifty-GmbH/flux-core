<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\AccountingSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class AccountingSettings extends SettingsComponent
{
    public AccountingSettingsForm $accountingSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'accountingSettingsForm';
    }
}
