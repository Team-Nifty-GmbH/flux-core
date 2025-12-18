<?php

use FluxErp\Models\Permission;

test('settings order types no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/order-types')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings order types page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.order-types.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/order-types')
        ->assertOk();
});

test('settings order types without permission', function (): void {
    Permission::findOrCreate('settings.order-types.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/order-types')
        ->assertForbidden();
});
