<?php

use FluxErp\Models\Permission;

test('settings countries no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/countries')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings countries page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.countries.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/countries')
        ->assertOk();
});

test('settings countries without permission', function (): void {
    Permission::findOrCreate('settings.countries.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/countries')
        ->assertForbidden();
});
