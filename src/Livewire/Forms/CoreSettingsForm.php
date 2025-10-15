<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Settings\CoreSettings;

class CoreSettingsForm extends SettingsForm
{
    public bool $install_done = false;

    public ?string $license_key = null;

    protected function getSettingsClass(): string
    {
        return CoreSettings::class;
    }
}
