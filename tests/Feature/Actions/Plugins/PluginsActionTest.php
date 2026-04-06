<?php

use FluxErp\Actions\Plugins\Install;
use FluxErp\Actions\Plugins\Uninstall;

test('install plugin requires packages', function (): void {
    Install::assertValidationErrors([], 'packages');
});

test('uninstall plugin requires packages', function (): void {
    Uninstall::assertValidationErrors([], 'packages');
});
