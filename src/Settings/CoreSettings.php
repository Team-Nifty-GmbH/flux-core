<?php

namespace FluxErp\Settings;

class CoreSettings extends FluxSettings
{
    public bool $formal_salutation;

    public bool $install_done;

    public ?string $license_key;

    public static function group(): string
    {
        return 'flux';
    }
}
