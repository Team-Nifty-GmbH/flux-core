<?php

use FluxErp\Models\Permission;
use FluxErp\Settings\CoreSettings;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    CoreSettings::fake([
        'install_done' => false,
        'license_key' => null,
        'formal_salutation' => false,
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
        'formal_salutation' => true,
    ]);

    $response->assertOk();

    $updatedSettings = app(CoreSettings::class);
    expect($updatedSettings->install_done)->toBe(true)
        ->and($updatedSettings->formal_salutation)->toBe(true);
});

test('update setting validation error', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/settings', [
        'settings_class' => CoreSettings::class,
        'install_done' => 'invalid-boolean-value',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['install_done']);
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
    $response->assertJsonValidationErrors(['settings_class']);
});
