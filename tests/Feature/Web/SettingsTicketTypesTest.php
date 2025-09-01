<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings ticket types no user', function (): void {
    $this->get('/settings/ticket-types')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings ticket types page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.ticket-types.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
        ->assertStatus(200);
});

test('settings ticket types without permission', function (): void {
    Permission::findOrCreate('settings.ticket-types.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
        ->assertStatus(403);
});
