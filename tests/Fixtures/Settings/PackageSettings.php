<?php

namespace FluxErp\Tests\Fixtures\Settings;

use FluxErp\Settings\FluxSettings;

class PackageSettings extends FluxSettings
{
    public bool $enabled;

    public static function group(): string
    {
        return 'package-fixture';
    }
}
