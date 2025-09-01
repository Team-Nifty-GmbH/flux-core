<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
use FluxErp\Models\Permission;

test('portal my profile no user', function (): void {
    $this->get(route('portal.my-profile'))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal my profile page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('my-profile.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.my-profile'))
        ->assertStatus(200);
});

test('portal my profile without permission', function (): void {
    Permission::findOrCreate('my-profile.get', 'address');

    $this->actingAs($this->user, 'address')->get(route('portal.my-profile'))
        ->assertStatus(403);
});
