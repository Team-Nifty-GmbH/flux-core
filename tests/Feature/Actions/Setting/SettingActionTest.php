<?php

use FluxErp\Actions\Setting\UpdateSetting;
use FluxErp\Facades\Settings;
use FluxErp\Settings\CoreSettings;
use FluxErp\Settings\SearchSettings;
use FluxErp\Tests\Fixtures\Settings\PackageSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Spatie\LaravelSettings\SettingsContainer;

test('update setting', function (): void {
    $result = UpdateSetting::make([
        'settings_class' => CoreSettings::class,
        'formal_salutation' => true,
    ])->validate()->execute();

    expect($result)->toBeInstanceOf(FluxErp\Settings\FluxSettings::class);
});

test('update setting requires settings_class', function (): void {
    UpdateSetting::assertValidationErrors([], 'settings_class');
});

test('a setting can be saved while one of its arrays is empty', function (): void {
    Settings::autoDiscover(
        dirname(__DIR__, 3) . '/Fixtures/Settings',
        'FluxErp\\Tests\\Fixtures\\Settings'
    );
    app(SettingsContainer::class)->clearCache()->registerBindings();
    app(SettingsMigrator::class)->inGroup('package-fixture', function (SettingsBlueprint $blueprint): void {
        $blueprint->add('enabled', true);
        $blueprint->add('layouts', ['sidebar']);
    });

    UpdateSetting::make([
        'settings_class' => PackageSettings::class,
        'layouts' => [],
    ])->validate()->execute();

    expect(app(PackageSettings::class)->refresh()->layouts)->toBe([]);
});

test('a setting still rejects a value of the wrong type', function (): void {
    UpdateSetting::assertValidationErrors([
        'settings_class' => SearchSettings::class,
        'embedder_dimensions' => 'not a number',
    ], 'embedder_dimensions');
});
