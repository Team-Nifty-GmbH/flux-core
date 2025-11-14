<?php

use FluxErp\Models\Permission;

test('settings tenants no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/tenants')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings tenants page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.tenants.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/tenants')
        ->assertOk();
});

test('settings tenants without permission', function (): void {
    Permission::findOrCreate('settings.tenants.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/tenants')
        ->assertForbidden();
});
