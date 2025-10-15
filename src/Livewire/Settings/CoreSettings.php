<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\Forms\CoreSettingsForm;
use FluxErp\Livewire\Support\SettingsComponent;
use FluxErp\Settings\CoreSettings as CoreSettingsClass;

class CoreSettings extends SettingsComponent
{
    public CoreSettingsForm $coreSettingsForm;

    protected function getFormPropertyName(): string
    {
        return 'coreSettingsForm';
    }

    protected function getSettingsClass(): string
    {
        return CoreSettingsClass::class;
    }
}
