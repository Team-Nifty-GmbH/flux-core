<?php

use FluxErp\Models\Permission;

test('settings languages no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/languages')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings languages page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.languages.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/languages')
        ->assertOk();
});

test('settings languages without permission', function (): void {
    Permission::findOrCreate('settings.languages.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/languages')
        ->assertForbidden();
});
