<?php

use FluxErp\Models\Permission;

test('settings currencies no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/currencies')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings currencies page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.currencies.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/currencies')
        ->assertOk();
});

test('settings currencies without permission', function (): void {
    Permission::findOrCreate('settings.currencies.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/currencies')
        ->assertForbidden();
});
