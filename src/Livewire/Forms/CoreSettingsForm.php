<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Settings\CoreSettings;

class CoreSettingsForm extends SettingsForm
{
    public bool $install_done = false;

    public ?string $license_key = null;

    public bool $formal_salutation = false;

    public function getSettingsClass(): string
    {
        return CoreSettings::class;
    }
}
