<?php

use FluxErp\Models\Permission;

test('calendars no user', function (): void {
    $this->actingAsGuest();

    $this->get('/calendars')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('calendars page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('calendars.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/calendars')
        ->assertOk();
});

test('calendars without permission', function (): void {
    Permission::findOrCreate('calendars.get', 'web');

    $this->actingAs($this->user, 'web')->get('/calendars')
        ->assertForbidden();
});
