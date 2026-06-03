<?php

namespace FluxErp\Settings;

class SecuritySettings extends FluxSettings
{
    public bool $force_two_factor;

    public bool $magic_login_links_enabled;

    public static function group(): string
    {
        return 'security';
    }
}
