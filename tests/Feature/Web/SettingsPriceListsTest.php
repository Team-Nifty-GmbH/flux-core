<?php

use FluxErp\Models\Permission;

test('settings price lists no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/price-lists')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings price lists page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.price-lists.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/price-lists')
        ->assertOk();
});

test('settings price lists without permission', function (): void {
    Permission::findOrCreate('settings.price-lists.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/price-lists')
        ->assertForbidden();
});
