<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\MailSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class MailSettings extends SettingsComponent
{
    public MailSettingsForm $mailSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'mailSettingsForm';
    }
}
