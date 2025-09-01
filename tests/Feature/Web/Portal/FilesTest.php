<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
use FluxErp\Models\Permission;

test('portal files no user', function (): void {
    $this->get(route('portal.files'))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal files page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('files.get', 'address'));

    $this->actingAs($this->user, 'address')->get(route('portal.files'))
        ->assertStatus(200);
});

test('portal files without permission', function (): void {
    Permission::findOrCreate('files.get', 'address');

    $this->actingAs($this->user, 'address')->get(route('portal.files'))
        ->assertStatus(403);
});
