<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Livewire\Forms\SettingsForm;
use FluxErp\Settings\CoreSettings;

class CoreSettingsForm extends SettingsForm
{
    public bool $formal_salutation = false;

    public bool $install_done = false;

    public ?string $license_key = null;

    public function getSettingsClass(): string
    {
        return CoreSettings::class;
    }
}
