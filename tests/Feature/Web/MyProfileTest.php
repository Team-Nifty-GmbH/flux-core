<?php

use FluxErp\Models\Permission;

test('my profile no user', function (): void {
    $this->actingAsGuest();

    $this->get('/my-profile')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('my profile page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('my-profile.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/my-profile')
        ->assertOk();
});

test('my profile without permission', function (): void {
    Permission::findOrCreate('my-profile.get', 'web');

    $this->actingAs($this->user, 'web')->get('/my-profile')
        ->assertForbidden();
});
