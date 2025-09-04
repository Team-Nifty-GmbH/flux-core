<?php

use FluxErp\Models\Permission;

test('settings clients no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/clients')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings clients page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.clients.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/clients')
        ->assertOk();
});

test('settings clients without permission', function (): void {
    Permission::findOrCreate('settings.clients.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/clients')
        ->assertForbidden();
});
