<?php

namespace FluxErp\Settings;

use FluxErp\Livewire\Settings\CoreSettings as CoreSettingsComponent;

class CoreSettings extends FluxSetting
{
    public bool $install_done;

    public ?string $license_key;

    public static function componentClass(): string
    {
        return CoreSettingsComponent::class;
    }

    public static function group(): string
    {
        return 'flux';
    }
}
