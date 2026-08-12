<?php

use FluxErp\Settings\CoreSettings;
use Spatie\LaravelSettings\SettingsContainer;

test('the settings of this package are registered in the container', function (): void {
    expect(app(SettingsContainer::class)->getSettingClasses())
        ->toContain(CoreSettings::class)
        ->and(app()->bound(CoreSettings::class))->toBeTrue();
});

test('a settings class is resolved once per request', function (): void {
    expect(app(CoreSettings::class))->toBe(app(CoreSettings::class));
});
