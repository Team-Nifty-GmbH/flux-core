<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\ActivityLogSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class ActivityLogSettings extends SettingsComponent
{
    public ActivityLogSettingsForm $activityLogSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'activityLogSettingsForm';
    }
}
