<?php

use FluxErp\Facades\Settings;
use FluxErp\Settings\CoreSettings;
use FluxErp\Tests\Fixtures\Settings\PackageSettings;
use Spatie\LaravelSettings\SettingsContainer;

test('the settings of this package are registered in the container', function (): void {
    expect(app(SettingsContainer::class)->getSettingClasses())
        ->toContain(CoreSettings::class)
        ->and(app()->bound(CoreSettings::class))->toBeTrue();
});

test('a settings class is resolved once per request', function (): void {
    expect(app(CoreSettings::class))->toBe(app(CoreSettings::class));
});

test('another package registers the settings of its own directory', function (): void {
    expect(app()->bound(PackageSettings::class))->toBeFalse();

    Settings::autoDiscover(
        dirname(__DIR__, 2) . '/Fixtures/Settings',
        'FluxErp\Tests\Fixtures\Settings'
    );

    app(SettingsContainer::class)->clearCache()->registerBindings();

    expect(Settings::all())->toContain(PackageSettings::class)
        ->and(app()->bound(PackageSettings::class))->toBeTrue();
});

test('the settings cache is on without anyone asking for it', function (): void {
    expect(config('settings.cache.enabled'))->toBeTrue()
        ->and(config('settings.cache.memo'))->toBeTrue();
});

test('the environment still decides', function (): void {
    config(['flux.settings.cache' => false, 'flux.settings.memo' => false]);

    (new FluxErp\Providers\SettingsServiceProvider(app()))->boot();

    expect(config('settings.cache.enabled'))->toBeFalse()
        ->and(config('settings.cache.memo'))->toBeFalse();
});
