<?php

use FluxErp\Settings\CoreSettings;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\SettingsContainer;

test('the settings of this package are registered in the container', function (): void {
    expect(app(SettingsContainer::class)->getSettingClasses())
        ->toContain(CoreSettings::class)
        ->and(app()->bound(CoreSettings::class))->toBeTrue();
});

test('a settings class reads its group once per request', function (): void {
    app()->forgetScopedInstances();

    $queries = 0;
    DB::listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'from `settings`')) {
            $queries++;
        }
    });

    foreach (range(1, 5) as $ignored) {
        app(CoreSettings::class)->toArray();
    }

    expect($queries)->toBe(1);
});
