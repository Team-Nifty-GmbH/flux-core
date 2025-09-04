<?php

use FluxErp\Models\Permission;

test('portal my profile no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.my-profile'))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal my profile page', function (): void {
    $this->address->givePermissionTo(Permission::findOrCreate('my-profile.get', 'address'));

    $this->actingAs($this->address, 'address')->get(route('portal.my-profile'))
        ->assertOk();
});

test('portal my profile without permission', function (): void {
    Permission::findOrCreate('my-profile.get', 'address');

    $this->actingAs($this->address, 'address')->get(route('portal.my-profile'))
        ->assertForbidden();
});
