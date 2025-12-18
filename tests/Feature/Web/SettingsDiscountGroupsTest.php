<?php

use FluxErp\Models\Permission;

test('settings discount groups no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/discount-groups')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings discount groups page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.discount-groups.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
        ->assertOk();
});

test('settings discount groups without permission', function (): void {
    Permission::findOrCreate('settings.discount-groups.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
        ->assertForbidden();
});
