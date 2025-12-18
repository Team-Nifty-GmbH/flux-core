<?php

use FluxErp\Models\Permission;

test('settings permissions no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/permissions')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings permissions page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.permissions.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/permissions')
        ->assertOk();
});

test('settings permissions without permission', function (): void {
    Permission::findOrCreate('settings.permissions.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/permissions')
        ->assertForbidden();
});
