<?php

use FluxErp\Livewire\Settings\ActivityLogSettings;
use FluxErp\Settings\ActivityLogSettings as ActivityLogSettingsModel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActivityLogSettings::class)
        ->assertOk();
});

test('can save settings', function (): void {
    ActivityLogSettingsModel::fake([
        'created_retention_days' => null,
        'updated_retention_days' => 365,
        'deleted_retention_days' => null,
        'other_retention_days' => 365,
    ]);

    Livewire::test(ActivityLogSettings::class)
        ->assertSet('activityLogSettingsForm.updated_retention_days', 365)
        ->set('activityLogSettingsForm.created_retention_days', 3650)
        ->set('activityLogSettingsForm.updated_retention_days', '')
        ->call('save')
        ->assertReturned(true);

    $settings = app(ActivityLogSettingsModel::class);

    expect($settings->created_retention_days)->toBe(3650)
        ->and($settings->updated_retention_days)->toBeNull()
        ->and($settings->other_retention_days)->toBe(365);
});

test('rejects a retention period below one day', function (): void {
    ActivityLogSettingsModel::fake([
        'created_retention_days' => null,
        'updated_retention_days' => 365,
        'deleted_retention_days' => null,
        'other_retention_days' => 365,
    ]);

    Livewire::test(ActivityLogSettings::class)
        ->set('activityLogSettingsForm.updated_retention_days', 0)
        ->call('save')
        ->assertReturned(false);

    expect(app(ActivityLogSettingsModel::class)->updated_retention_days)->toBe(365);
});
