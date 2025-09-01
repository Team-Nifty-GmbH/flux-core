<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings users no user', function (): void {
    $this->get('/settings/users')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings users page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.users.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/users')
        ->assertStatus(200);
});

test('settings users without permission', function (): void {
    Permission::findOrCreate('settings.users.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/users')
        ->assertStatus(403);
});
