<?php

use FluxErp\Models\Permission;
use FluxErp\Settings\CoreSettings;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    CoreSettings::fake([
        'install_done' => false,
        'license_key' => null,
    ]);

    $this->permissions = [
        'update' => Permission::findOrCreate('api.settings.put', 'sanctum'),
    ];
});

test('update setting', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'settings_class' => CoreSettings::class,
        'install_done' => true,
    ]);

    $response->assertOk();

    $updatedSettings = app(CoreSettings::class);
    expect($updatedSettings->install_done)->toBe(true);
});

test('update setting validation error', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'settings_class' => CoreSettings::class,
        'install_done' => 'invalid-boolean-value',
    ]);

    $response->assertUnprocessable();
});

test('update setting without permission', function (): void {
    $this->user->revokePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'settings_class' => CoreSettings::class,
        'install_done' => true,
    ]);

    $response->assertForbidden();
});

test('update setting missing settings_class', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'install_done' => true,
    ]);

    $response->assertUnprocessable();
});
