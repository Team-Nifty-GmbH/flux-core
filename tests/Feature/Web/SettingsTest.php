<?php

use FluxErp\Models\Permission;

test('settings no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/tenants')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings without permission', function (): void {
    Permission::findOrCreate('settings.tenants.get', 'web');

    $this->actingAs($this->user, guard: 'web')->get('/settings/tenants')
        ->assertForbidden();
});
