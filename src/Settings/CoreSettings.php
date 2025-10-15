<?php

namespace FluxErp\Settings;

class CoreSettings extends FluxSetting
{
    public bool $install_done;

    public ?string $license_key;

    public static function group(): string
    {
        return 'flux';
    }
}
