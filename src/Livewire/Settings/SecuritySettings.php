<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\SecuritySettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class SecuritySettings extends SettingsComponent
{
    public SecuritySettingsForm $securitySettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'securitySettingsForm';
    }
}
