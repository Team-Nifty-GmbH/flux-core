<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('my profile no user', function (): void {
    $this->get('/my-profile')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('my profile page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('my-profile.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/my-profile')
        ->assertStatus(200);
});

test('my profile without permission', function (): void {
    Permission::findOrCreate('my-profile.get', 'web');

    $this->actingAs($this->user, 'web')->get('/my-profile')
        ->assertStatus(403);
});
