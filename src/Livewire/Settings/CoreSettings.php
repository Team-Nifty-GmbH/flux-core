<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\Settings\CoreSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;

class CoreSettings extends SettingsComponent
{
    public CoreSettingsForm $coreSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'coreSettingsForm';
    }
}
