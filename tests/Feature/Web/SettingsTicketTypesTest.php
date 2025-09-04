<?php

use FluxErp\Models\Permission;

test('settings ticket types no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/ticket-types')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings ticket types page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.ticket-types.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
        ->assertOk();
});

test('settings ticket types without permission', function (): void {
    Permission::findOrCreate('settings.ticket-types.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
        ->assertForbidden();
});
