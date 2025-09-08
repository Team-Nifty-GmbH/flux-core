<?php

use FluxErp\Models\Permission;

test('settings no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/clients')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings without permission', function (): void {
    Permission::findOrCreate('settings.clients.get', 'web');

    $this->actingAs($this->user, guard: 'web')->get('/settings/clients')
        ->assertForbidden();
});
