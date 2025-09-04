<?php

use FluxErp\Models\Permission;

test('settings users no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/users')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings users page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.users.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/users')
        ->assertOk();
});

test('settings users without permission', function (): void {
    Permission::findOrCreate('settings.users.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/users')
        ->assertForbidden();
});
