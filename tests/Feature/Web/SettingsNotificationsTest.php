<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings notifications no user', function (): void {
    $this->get('/settings/notifications')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings notifications page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.notifications.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/notifications')
        ->assertStatus(200);
});

test('settings notifications without permission', function (): void {
    Permission::findOrCreate('settings.notifications.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/notifications')
        ->assertStatus(403);
});
