<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings clients no user', function (): void {
    $this->get('/settings/clients')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings clients page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.clients.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/clients')
        ->assertStatus(200);
});

test('settings clients without permission', function (): void {
    Permission::findOrCreate('settings.clients.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/clients')
        ->assertStatus(403);
});
