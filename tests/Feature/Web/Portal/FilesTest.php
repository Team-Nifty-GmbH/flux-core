<?php

use FluxErp\Models\Permission;

test('portal files no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.files'))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal files page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('files.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.files'))
        ->assertOk();
});

test('portal files without permission', function (): void {
    Permission::findOrCreate('files.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.files'))
        ->assertForbidden();
});
