<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\SecuritySettings;

class SecuritySettingsForm extends SettingsForm
{
    public bool $force_two_factor = false;

    public bool $magic_login_links_enabled = true;

    public function getSettingsClass(): string
    {
        return SecuritySettings::class;
    }
}
