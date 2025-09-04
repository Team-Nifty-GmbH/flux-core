<?php

use FluxErp\Models\Permission;

test('settings notifications no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/notifications')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings notifications page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.notifications.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/notifications')
        ->assertOk();
});

test('settings notifications without permission', function (): void {
    Permission::findOrCreate('settings.notifications.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/notifications')
        ->assertForbidden();
});
