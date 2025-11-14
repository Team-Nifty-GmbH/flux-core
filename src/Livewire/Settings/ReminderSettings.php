<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\ReminderSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class ReminderSettings extends SettingsComponent
{
    public ReminderSettingsForm $reminderSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'reminderSettingsForm';
    }
}
