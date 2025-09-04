<?php

use FluxErp\Models\Permission;

test('settings logs no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/logs')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings logs page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.logs.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/logs')
        ->assertOk();
});

test('settings logs without permission', function (): void {
    Permission::findOrCreate('settings.logs.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/logs')
        ->assertForbidden();
});
